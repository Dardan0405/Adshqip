<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoAnalyticsController extends Controller
{
    private const VIDEO_TYPES = ['video', 'vast', 'clip'];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        return view('admin.video-analytics.index', [
            'summary' => $this->buildSummary($filters),
            'rows' => $this->buildRows($filters),
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->buildRows($filters, false);
        $filename = 'video_analytics_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ad ID', 'Ad Name', 'Campaign', 'Type', 'Viewers', 'Starts', 'First Quartile', 'Midpoint', 'Third Quartile', 'Completes', 'Skips', 'Completion Rate (%)', 'Avg Progress (%)', 'Last Event']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->ad_id,
                    $row->ad_name,
                    $row->campaign_name,
                    $row->ad_type,
                    $row->unique_viewers,
                    $row->starts,
                    $row->first_quartile,
                    $row->midpoint,
                    $row->third_quartile,
                    $row->completes,
                    $row->skips,
                    number_format((float) $row->completion_rate, 2, '.', ''),
                    number_format((float) $row->avg_progress, 2, '.', ''),
                    $row->last_event_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'event_name' => ['nullable', 'string', 'max:50'],
        ]);
    }

    private function dateBounds(array $filters): array
    {
        return [
            ($filters['start_date'] ?? now()->subDays(30)->toDateString()) . ' 00:00:00',
            ($filters['end_date'] ?? now()->toDateString()) . ' 23:59:59',
        ];
    }

    private function baseQuery(array $filters)
    {
        [$startDate, $endDate] = $this->dateBounds($filters);

        $query = DB::table('aq_video_tracking as vt')
            ->join('aq_ads as ads', 'vt.ad_id', '=', 'ads.id')
            ->leftJoin('aq_campaigns as campaigns', 'ads.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_vast_events as events', 'vt.event_id', '=', 'events.id')
            ->whereIn('ads.ad_type', self::VIDEO_TYPES)
            ->whereBetween('vt.created_at', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('ads.name', 'like', '%' . $search . '%')
                    ->orWhere('campaigns.name', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['event_name'])) {
            $query->where('events.event_name', $filters['event_name']);
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $summary = $this->baseQuery($filters)
            ->selectRaw('
                COUNT(vt.id) as total_events,
                COUNT(DISTINCT vt.viewer_id) as unique_viewers,
                COUNT(DISTINCT vt.ad_id) as active_video_ads,
                SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END) as starts,
                SUM(CASE WHEN events.event_name = "complete" THEN 1 ELSE 0 END) as completes,
                AVG(COALESCE(vt.progress_percent, 0)) as avg_progress
            ')
            ->first();

        $starts = (int) ($summary->starts ?? 0);
        $completes = (int) ($summary->completes ?? 0);

        return [
            'video_ads' => (int) ($summary->active_video_ads ?? 0),
            'events' => (int) ($summary->total_events ?? 0),
            'viewers' => (int) ($summary->unique_viewers ?? 0),
            'starts' => $starts,
            'completes' => $completes,
            'completion_rate' => $starts > 0 ? round(($completes / $starts) * 100, 2) : 0,
            'avg_progress' => round((float) ($summary->avg_progress ?? 0), 2),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw('
                vt.ad_id,
                ads.name as ad_name,
                ads.ad_type,
                COALESCE(campaigns.name, "Unassigned") as campaign_name,
                COUNT(vt.id) as total_events,
                COUNT(DISTINCT vt.viewer_id) as unique_viewers,
                SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END) as starts,
                SUM(CASE WHEN events.event_name = "firstQuartile" THEN 1 ELSE 0 END) as first_quartile,
                SUM(CASE WHEN events.event_name = "midpoint" THEN 1 ELSE 0 END) as midpoint,
                SUM(CASE WHEN events.event_name = "thirdQuartile" THEN 1 ELSE 0 END) as third_quartile,
                SUM(CASE WHEN events.event_name = "complete" THEN 1 ELSE 0 END) as completes,
                SUM(CASE WHEN events.event_name = "skip" THEN 1 ELSE 0 END) as skips,
                CASE WHEN SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END) > 0
                    THEN ROUND((SUM(CASE WHEN events.event_name = "complete" THEN 1 ELSE 0 END) / SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END)) * 100, 2)
                    ELSE 0
                END as completion_rate,
                AVG(COALESCE(vt.progress_percent, 0)) as avg_progress,
                MAX(vt.created_at) as last_event_at
            ')
            ->groupBy('vt.ad_id', 'ads.name', 'ads.ad_type', 'campaigns.name')
            ->orderByDesc('total_events');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }
}
