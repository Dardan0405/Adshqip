<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublisherAppReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        if (! Schema::hasTable('aq_telegram_mini_app_events')) {
            return view('publisher.reports.apps', [
                'reports'  => $this->emptyPaginator($request),
                'summary'  => $this->emptySummary(),
                'defaults' => $this->defaults(),
            ]);
        }

        $reports = $this->buildRows($filters);
        $summary = $this->buildSummary($filters);

        return view('publisher.reports.apps', [
            'reports'  => $reports,
            'summary'  => $summary,
            'defaults' => $this->defaults(),
        ]);
    }

    public function export(Request $request)
    {
        $filters  = $this->validatedFilters($request);
        $rows     = Schema::hasTable('aq_telegram_mini_app_events')
            ? $this->buildRows($filters, paginate: false)
            : collect();
        $filename = 'publisher_app_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Application Name', 'Application URL', 'Impressions', 'Clicks', 'Earnings', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->app_name,
                    $row->app_url,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    number_format((float) $row->earnings, 4, '.', ''),
                    number_format((float) $row->ecpm, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search'     => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function baseQuery(array $filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate   = $filters['end_date']   ?? now()->toDateString();

        $query = DB::table('aq_telegram_mini_app_events as events')
            ->join('aq_telegram_mini_apps as apps', 'events.mini_app_id', '=', 'apps.id')
            ->where('apps.user_id', Auth::id())
            ->where('apps.is_deleted', false)
            ->whereDate('events.created_at', '>=', $startDate)
            ->whereDate('events.created_at', '<=', $endDate);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('apps.app_name', 'like', '%' . $search . '%')
                  ->orWhere('apps.app_url', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw("
                COUNT(DISTINCT apps.id) as total_apps,
                SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END) as impressions,
                SUM(CASE WHEN events.event_type = 'ad_click' THEN 1 ELSE 0 END) as clicks,
                COALESCE(SUM(events.revenue), 0) as earnings,
                CASE
                    WHEN SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END) > 0
                    THEN ROUND((COALESCE(SUM(events.revenue), 0) / SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->first();

        return [
            'total_apps'  => (int) ($row->total_apps ?? 0),
            'impressions' => (int) ($row->impressions ?? 0),
            'clicks'      => (int) ($row->clicks ?? 0),
            'earnings'    => (float) ($row->earnings ?? 0),
            'ecpm'        => (float) ($row->ecpm ?? 0),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw("
                DATE(events.created_at) as date,
                apps.app_name,
                apps.app_url,
                SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END) as impressions,
                SUM(CASE WHEN events.event_type = 'ad_click' THEN 1 ELSE 0 END) as clicks,
                COALESCE(SUM(events.revenue), 0) as earnings,
                CASE
                    WHEN SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END) > 0
                    THEN ROUND((COALESCE(SUM(events.revenue), 0) / SUM(CASE WHEN events.event_type = 'ad_impression' THEN 1 ELSE 0 END)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->groupBy(DB::raw('DATE(events.created_at)'), 'apps.id', 'apps.app_name', 'apps.app_url')
            ->orderByDesc('date')
            ->orderByDesc('impressions');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }

    private function emptySummary(): array
    {
        return ['total_apps' => 0, 'impressions' => 0, 'clicks' => 0, 'earnings' => 0.0, 'ecpm' => 0.0];
    }

    private function emptyPaginator(Request $request)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1, [
            'path' => $request->url(),
        ]);
    }

    private function defaults(): array
    {
        return [
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date'   => now()->toDateString(),
        ];
    }
}
