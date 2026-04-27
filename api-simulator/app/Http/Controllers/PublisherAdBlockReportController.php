<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublisherAdBlockReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        if (! Schema::hasTable('aq_stats_daily')) {
            return view('publisher.reports.adblocks', [
                'reports'  => $this->emptyPaginator($request),
                'summary'  => $this->emptySummary(),
                'defaults' => $this->defaults(),
            ]);
        }

        return view('publisher.reports.adblocks', [
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
        $filename = 'publisher_adblock_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Impressions', 'Clicks', 'Earnings', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->zone_name,
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

        $query = DB::table('aq_stats_daily as s')
            ->leftJoin('aq_zones as z', 's.zone_id', '=', 'z.id')
            ->where('s.publisher_id', Auth::id())
            ->whereNotNull('s.zone_id')
            ->whereDate('s.date', '>=', $startDate)
            ->whereDate('s.date', '<=', $endDate);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('z.name', 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw("
                COUNT(DISTINCT s.zone_id) as total_zones,
                COALESCE(SUM(s.impressions), 0) as impressions,
                COALESCE(SUM(s.clicks), 0) as clicks,
                COALESCE(SUM(s.publisher_earnings), 0) as earnings,
                CASE
                    WHEN COALESCE(SUM(s.impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(s.publisher_earnings), 0) / SUM(s.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->first();

        return [
            'total_zones' => (int) ($row->total_zones ?? 0),
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
                z.id as zone_id,
                COALESCE(z.name, CONCAT('Zone #', s.zone_id)) as zone_name,
                COALESCE(SUM(s.impressions), 0) as impressions,
                COALESCE(SUM(s.clicks), 0) as clicks,
                COALESCE(SUM(s.publisher_earnings), 0) as earnings,
                CASE
                    WHEN COALESCE(SUM(s.impressions), 0) > 0
                    THEN ROUND((COALESCE(SUM(s.publisher_earnings), 0) / SUM(s.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->groupBy('s.zone_id', 'z.id', 'z.name')
            ->orderByDesc('earnings')
            ->orderByDesc('impressions');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }

    private function emptySummary(): array
    {
        return ['total_zones' => 0, 'impressions' => 0, 'clicks' => 0, 'earnings' => 0.0, 'ecpm' => 0.0];
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
