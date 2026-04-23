<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoCreativeReportController extends Controller
{
    private const VIDEO_TYPES = ['video', 'vast', 'clip'];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        return view('advertiser.video-creative-reports.index', [
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
        $filename = 'video_creative_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Creative', 'Impressions', 'View 25%', 'View 50%', 'View 75%', 'Complete', 'Pause', 'Resume', 'Full Screen', 'Unmute', 'Mute']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->creative,
                    (int) $row->impressions,
                    (int) $row->view_25,
                    (int) $row->view_50,
                    (int) $row->view_75,
                    (int) $row->complete,
                    (int) $row->pause,
                    (int) $row->resume,
                    (int) $row->full_screen,
                    (int) $row->unmute,
                    (int) $row->mute,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
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

        $query = DB::table('aq_ads as ads')
            ->join('aq_campaigns as campaigns', 'ads.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_ad_creatives as creatives', function ($join) {
                $join->on('creatives.ad_id', '=', 'ads.id')
                    ->where('creatives.is_primary', true);
            })
            ->leftJoin('aq_video_tracking as vt', function ($join) use ($startDate, $endDate) {
                $join->on('vt.ad_id', '=', 'ads.id')
                    ->whereBetween('vt.created_at', [$startDate, $endDate]);
            })
            ->leftJoin('aq_vast_events as events', 'vt.event_id', '=', 'events.id')
            ->where('campaigns.advertiser_id', auth()->id())
            ->where('ads.is_deleted', false)
            ->where(function ($videoQuery) {
                $videoQuery
                    ->whereIn('ads.ad_type', self::VIDEO_TYPES)
                    ->orWhere('creatives.file_type', 'video')
                    ->orWhere('creatives.mime_type', 'like', 'video/%')
                    ->orWhere(function ($urlQuery) {
                        $urlQuery
                            ->whereNotNull('creatives.video_url')
                            ->where('creatives.video_url', '<>', '');
                    });
            });

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('ads.name', 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $summary = $this->baseQuery($filters)
            ->selectRaw('
                COUNT(DISTINCT ads.id) as creatives,
                CASE
                    WHEN COUNT(DISTINCT vt.impression_id) > 0 THEN COUNT(DISTINCT vt.impression_id)
                    ELSE COALESCE(SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END), 0)
                END as impressions,
                COALESCE(SUM(CASE WHEN events.event_name = "complete" THEN 1 ELSE 0 END), 0) as complete,
                COALESCE(SUM(CASE WHEN events.event_name = "pause" THEN 1 ELSE 0 END), 0) as pause,
                COALESCE(SUM(CASE WHEN events.event_name = "resume" THEN 1 ELSE 0 END), 0) as resume,
                COALESCE(SUM(CASE WHEN events.event_name IN ("fullscreen", "fullScreen", "fullscreenOn") THEN 1 ELSE 0 END), 0) as full_screen,
                COALESCE(SUM(CASE WHEN events.event_name = "unmute" THEN 1 ELSE 0 END), 0) as unmute,
                COALESCE(SUM(CASE WHEN events.event_name = "mute" THEN 1 ELSE 0 END), 0) as mute
            ')
            ->first();

        return [
            'creatives' => (int) ($summary->creatives ?? 0),
            'impressions' => (int) ($summary->impressions ?? 0),
            'complete' => (int) ($summary->complete ?? 0),
            'pause' => (int) ($summary->pause ?? 0),
            'resume' => (int) ($summary->resume ?? 0),
            'full_screen' => (int) ($summary->full_screen ?? 0),
            'unmute' => (int) ($summary->unmute ?? 0),
            'mute' => (int) ($summary->mute ?? 0),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw('
                ads.id as ad_id,
                ads.name as creative,
                ads.ad_type,
                CASE
                    WHEN COUNT(DISTINCT vt.impression_id) > 0 THEN COUNT(DISTINCT vt.impression_id)
                    ELSE COALESCE(SUM(CASE WHEN events.event_name = "start" THEN 1 ELSE 0 END), 0)
                END as impressions,
                COALESCE(SUM(CASE WHEN events.event_name = "firstQuartile" THEN 1 ELSE 0 END), 0) as view_25,
                COALESCE(SUM(CASE WHEN events.event_name = "midpoint" THEN 1 ELSE 0 END), 0) as view_50,
                COALESCE(SUM(CASE WHEN events.event_name = "thirdQuartile" THEN 1 ELSE 0 END), 0) as view_75,
                COALESCE(SUM(CASE WHEN events.event_name = "complete" THEN 1 ELSE 0 END), 0) as complete,
                COALESCE(SUM(CASE WHEN events.event_name = "pause" THEN 1 ELSE 0 END), 0) as pause,
                COALESCE(SUM(CASE WHEN events.event_name = "resume" THEN 1 ELSE 0 END), 0) as resume,
                COALESCE(SUM(CASE WHEN events.event_name IN ("fullscreen", "fullScreen", "fullscreenOn") THEN 1 ELSE 0 END), 0) as full_screen,
                COALESCE(SUM(CASE WHEN events.event_name = "unmute" THEN 1 ELSE 0 END), 0) as unmute,
                COALESCE(SUM(CASE WHEN events.event_name = "mute" THEN 1 ELSE 0 END), 0) as mute,
                MAX(vt.created_at) as last_event_at
            ')
            ->groupBy('ads.id', 'ads.name', 'ads.ad_type')
            ->orderByDesc('last_event_at');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }
}
