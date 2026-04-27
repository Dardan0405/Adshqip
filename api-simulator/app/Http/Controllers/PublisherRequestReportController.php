<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublisherRequestReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        if (! Schema::hasTable('aq_stats_daily')) {
            return view('publisher.reports.requests', [
                'reports'  => $this->emptyPaginator($request),
                'summary'  => $this->emptySummary(),
                'defaults' => $this->defaults(),
            ]);
        }

        return view('publisher.reports.requests', [
            'reports'  => $this->buildRows($filters),
            'summary'  => $this->buildSummary($filters),
            'defaults' => $this->defaults(),
        ]);
    }

    public function export(Request $request)
    {
        $filters  = $this->validatedFilters($request);
        $rows     = Schema::hasTable('aq_stats_daily')
            ? $this->buildRows($filters, paginate: false)
            : collect();
        $filename = 'publisher_request_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Request', 'Impressions', 'Clicks', 'Conversions', 'Revenue', 'CTR', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->date,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    (int) $row->conversions,
                    number_format((float) $row->revenue, 4, '.', ''),
                    number_format((float) $row->ctr, 2, '.', '') . '%',
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

        $query = DB::table('aq_stats_daily')
            ->where('publisher_id', Auth::id())
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if (! empty($filters['search'])) {
            $query->whereDate('date', 'like', '%' . trim((string) $filters['search']) . '%');
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw("
                COUNT(*) as requests,
                COALESCE(SUM(impressions), 0) as impressions,
                COALESCE(SUM(clicks), 0) as clicks,
                COALESCE(SUM(conversions), 0) as conversions,
                COALESCE(SUM(publisher_earnings), 0) as revenue,
                CASE
                    WHEN COALESCE(SUM(impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(clicks), 0) / SUM(impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN COALESCE(SUM(impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(publisher_earnings), 0) / SUM(impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->first();

        return [
            'requests'    => (int) ($row->requests ?? 0),
            'impressions' => (int) ($row->impressions ?? 0),
            'clicks'      => (int) ($row->clicks ?? 0),
            'conversions' => (int) ($row->conversions ?? 0),
            'revenue'     => (float) ($row->revenue ?? 0),
            'ctr'         => (float) ($row->ctr ?? 0),
            'ecpm'        => (float) ($row->ecpm ?? 0),
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw("
                date,
                COUNT(*) as requests,
                COALESCE(SUM(impressions), 0) as impressions,
                COALESCE(SUM(clicks), 0) as clicks,
                COALESCE(SUM(conversions), 0) as conversions,
                COALESCE(SUM(publisher_earnings), 0) as revenue,
                CASE
                    WHEN COALESCE(SUM(impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(clicks), 0) / SUM(impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN COALESCE(SUM(impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(publisher_earnings), 0) / SUM(impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->groupBy('date')
            ->orderByDesc('date');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }

    private function emptySummary(): array
    {
        return [
            'requests' => 0, 'impressions' => 0, 'clicks' => 0,
            'conversions' => 0, 'revenue' => 0.0, 'ctr' => 0.0, 'ecpm' => 0.0,
        ];
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
