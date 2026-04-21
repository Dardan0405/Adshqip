<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        return view('admin.geo-analytics.index', [
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
        $filename = 'geo_analytics_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Country Code', 'Impressions', 'Clicks', 'Conversions', 'Revenue', 'Publisher Earnings', 'CTR (%)', 'ECPM']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->country_code,
                    $row->impressions,
                    $row->clicks,
                    $row->conversions,
                    number_format((float) $row->revenue, 4, '.', ''),
                    number_format((float) $row->publisher_earnings, 4, '.', ''),
                    number_format((float) $row->ctr, 2, '.', ''),
                    number_format((float) $row->ecpm, 4, '.', ''),
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
            'device_type' => ['nullable', 'in:desktop,mobile,tablet'],
        ]);
    }

    private function baseQuery(array $filters)
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $query = DB::table('aq_stats_daily as stats')
            ->whereBetween('stats.date', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $query->where('stats.country_code', 'like', '%' . strtoupper(trim((string) $filters['search'])) . '%');
        }

        if (! empty($filters['device_type'])) {
            $query->where('stats.device_type', $filters['device_type']);
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $summary = $this->baseQuery($filters)
            ->selectRaw('
                COUNT(DISTINCT COALESCE(stats.country_code, "N/A")) as countries,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.revenue), 0) as revenue
            ')
            ->first();

        $impressions = (float) ($summary->impressions ?? 0);
        $revenue = (float) ($summary->revenue ?? 0);

        return [
            'countries' => (int) ($summary->countries ?? 0),
            'impressions' => (int) $impressions,
            'clicks' => (int) ($summary->clicks ?? 0),
            'conversions' => (int) ($summary->conversions ?? 0),
            'revenue' => round($revenue, 2),
            'ctr' => $impressions > 0 ? round((((float) ($summary->clicks ?? 0)) / $impressions) * 100, 2) : 0,
        ];
    }

    private function buildRows(array $filters, bool $paginate = true)
    {
        $query = $this->baseQuery($filters)
            ->selectRaw('
                COALESCE(stats.country_code, "N/A") as country_code,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.revenue), 0) as revenue,
                COALESCE(SUM(stats.publisher_earnings), 0) as publisher_earnings,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) / SUM(stats.impressions)) * 1000, 4) ELSE 0 END as ecpm
            ')
            ->groupBy('stats.country_code')
            ->orderByDesc('impressions');

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate(20)->withQueryString();
    }
}
