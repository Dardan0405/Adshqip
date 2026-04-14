<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnvironmentPerformanceController extends Controller
{
    private const ENVIRONMENT_LABELS = [
        'desktop' => 'Desktop',
        'mobile' => 'Mobile',
        'tablet' => 'Tablet',
        'tv' => 'Smart TV',
        'console' => 'Console',
        'unknown' => 'Unknown',
    ];

    private const ENVIRONMENT_ICONS = [
        'desktop' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'mobile' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'tablet' => 'M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        'tv' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'console' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
        'unknown' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $summary = $this->buildSummary($filters);
        $environmentData = $this->buildEnvironmentData($filters);

        return view('admin.environment-performance.index', [
            'summary' => $summary,
            'environmentData' => $environmentData,
            'environmentLabels' => self::ENVIRONMENT_LABELS,
            'environmentIcons' => self::ENVIRONMENT_ICONS,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $environmentData = $this->buildEnvironmentData($filters);

        $filename = 'environment_performance_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($environmentData) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Environment', 'Impressions', 'Clicks', 'Conversions', 'Unique Impressions', 'Unique Clicks', 'Spend', 'CTR (%)', 'Earnings', 'ECPM']);

            foreach ($environmentData as $row) {
                fputcsv($file, [
                    self::ENVIRONMENT_LABELS[$row->device_type] ?? ucfirst($row->device_type),
                    $row->impressions,
                    $row->clicks,
                    $row->conversions,
                    $row->unique_impressions,
                    $row->unique_clicks,
                    number_format($row->spend, 2),
                    number_format($row->ctr, 2),
                    number_format($row->earnings, 2),
                    number_format($row->ecpm, 2),
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
        ]);
    }

    private function baseQuery(array $filters)
    {
        $query = DB::table('aq_stats_daily as stats');

        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $query->whereBetween('stats.date', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $search = strtolower(trim((string) $filters['search']));
            $query->where('stats.device_type', 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $query = $this->baseQuery($filters);

        $summary = $query
            ->selectRaw('
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.unique_impressions), 0) as unique_impressions,
                COALESCE(SUM(stats.unique_clicks), 0) as unique_clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.revenue), 0) as spend,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings
            ')
            ->first();

        $impressions = (float) ($summary->impressions ?? 0);
        $clicks = (float) ($summary->clicks ?? 0);
        $spend = (float) ($summary->spend ?? 0);

        return [
            'impressions' => (int) $impressions,
            'clicks' => (int) $clicks,
            'unique_impressions' => (int) ($summary->unique_impressions ?? 0),
            'unique_clicks' => (int) ($summary->unique_clicks ?? 0),
            'conversions' => (int) ($summary->conversions ?? 0),
            'spend' => $spend,
            'earnings' => (float) ($summary->earnings ?? 0),
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
            'ecpm' => $impressions > 0 ? round(($spend / $impressions) * 1000, 2) : 0,
        ];
    }

    private function buildEnvironmentData(array $filters)
    {
        $query = $this->baseQuery($filters);

        return $query
            ->selectRaw("
                COALESCE(LOWER(stats.device_type), 'unknown') as device_type,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.unique_impressions), 0) as unique_impressions,
                COALESCE(SUM(stats.unique_clicks), 0) as unique_clicks,
                COALESCE(SUM(stats.revenue), 0) as spend,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) / SUM(stats.impressions)) * 1000, 2) ELSE 0 END as ecpm
            ")
            ->groupBy(DB::raw("COALESCE(LOWER(stats.device_type), 'unknown')"))
            ->orderByDesc('impressions')
            ->get();
    }
}
