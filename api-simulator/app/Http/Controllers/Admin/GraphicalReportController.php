<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraphicalReportController extends Controller
{
    private const COUNTRY_NAMES = [
        'AL' => 'Albania',
        'AD' => 'Andorra',
        'AT' => 'Austria',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BA' => 'Bosnia and Herzegovina',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'XK' => 'Kosovo',
        'LV' => 'Latvia',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'ME' => 'Montenegro',
        'NL' => 'Netherlands',
        'MK' => 'North Macedonia',
        'NO' => 'Norway',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'SM' => 'San Marino',
        'RS' => 'Serbia',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'TR' => 'Turkey',
        'UA' => 'Ukraine',
        'GB' => 'United Kingdom',
        'VA' => 'Vatican City',
        'US' => 'United States',
        'CA' => 'Canada',
        'MX' => 'Mexico',
        'BR' => 'Brazil',
        'AR' => 'Argentina',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'JP' => 'Japan',
        'CN' => 'China',
        'IN' => 'India',
        'KR' => 'South Korea',
        'SG' => 'Singapore',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'ZA' => 'South Africa',
        'EG' => 'Egypt',
        'NG' => 'Nigeria',
        'KE' => 'Kenya',
    ];

    private const COUNTRY_COORDS = [
        'AL' => ['lat' => 41.1533, 'lng' => 20.1683],
        'AT' => ['lat' => 47.5162, 'lng' => 14.5501],
        'BA' => ['lat' => 43.9159, 'lng' => 17.6791],
        'BG' => ['lat' => 42.7339, 'lng' => 25.4858],
        'HR' => ['lat' => 45.1, 'lng' => 15.2],
        'CZ' => ['lat' => 49.8175, 'lng' => 15.473],
        'DK' => ['lat' => 56.2639, 'lng' => 9.5018],
        'FI' => ['lat' => 61.9241, 'lng' => 25.7482],
        'FR' => ['lat' => 46.2276, 'lng' => 2.2137],
        'DE' => ['lat' => 51.1657, 'lng' => 10.4515],
        'GR' => ['lat' => 39.0742, 'lng' => 21.8243],
        'HU' => ['lat' => 47.1625, 'lng' => 19.5033],
        'IT' => ['lat' => 41.8719, 'lng' => 12.5674],
        'XK' => ['lat' => 42.6026, 'lng' => 20.903],
        'ME' => ['lat' => 42.7087, 'lng' => 19.3744],
        'MK' => ['lat' => 41.5124, 'lng' => 21.4473],
        'NL' => ['lat' => 52.1326, 'lng' => 5.2913],
        'PL' => ['lat' => 51.9194, 'lng' => 19.1451],
        'PT' => ['lat' => 39.3999, 'lng' => -8.2245],
        'RO' => ['lat' => 45.9432, 'lng' => 24.9668],
        'RS' => ['lat' => 44.0165, 'lng' => 21.0059],
        'SK' => ['lat' => 48.669, 'lng' => 19.699],
        'SI' => ['lat' => 46.1512, 'lng' => 14.9955],
        'ES' => ['lat' => 40.4637, 'lng' => -3.7492],
        'SE' => ['lat' => 60.1282, 'lng' => 18.6435],
        'CH' => ['lat' => 46.8182, 'lng' => 8.2275],
        'TR' => ['lat' => 38.9637, 'lng' => 35.2433],
        'GB' => ['lat' => 55.3781, 'lng' => -3.436],
        'US' => ['lat' => 37.0902, 'lng' => -95.7129],
        'CA' => ['lat' => 56.1304, 'lng' => -106.3468],
        'AU' => ['lat' => -25.2744, 'lng' => 133.7751],
        'JP' => ['lat' => 36.2048, 'lng' => 138.2529],
        'CN' => ['lat' => 35.8617, 'lng' => 104.1954],
        'IN' => ['lat' => 20.5937, 'lng' => 78.9629],
        'BR' => ['lat' => -14.235, 'lng' => -51.9253],
    ];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $summary = $this->buildSummary($filters);
        $countryData = $this->buildCountryData($filters);

        $mapData = $countryData->map(function ($row) {
            $code = $row->country_code ?? 'N/A';
            $coords = self::COUNTRY_COORDS[$code] ?? null;

            return [
                'country_code' => $code,
                'country_name' => self::COUNTRY_NAMES[$code] ?? $code,
                'impressions' => (int) $row->impressions,
                'clicks' => (int) $row->clicks,
                'conversions' => (int) $row->conversions,
                'ctr' => (float) $row->ctr,
                'lat' => $coords['lat'] ?? null,
                'lng' => $coords['lng'] ?? null,
            ];
        })->filter(fn($item) => $item['lat'] !== null)->values();

        return view('admin.graphical-reports.index', [
            'summary' => $summary,
            'countryData' => $countryData,
            'mapData' => $mapData,
            'countryNames' => self::COUNTRY_NAMES,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $countryData = $this->buildCountryData($filters);

        $filename = 'graphical_report_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($countryData) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Country', 'Country Code', 'Impressions', 'Clicks', 'Conversions', 'CTR (%)']);

            foreach ($countryData as $row) {
                fputcsv($file, [
                    self::COUNTRY_NAMES[$row->country_code] ?? $row->country_code,
                    $row->country_code,
                    $row->impressions,
                    $row->clicks,
                    $row->conversions,
                    number_format($row->ctr, 2),
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
            $search = strtoupper(trim((string) $filters['search']));
            $query->where('stats.country_code', 'like', '%' . $search . '%');
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
                COALESCE(SUM(stats.conversions), 0) as conversions
            ')
            ->first();

        $impressions = (float) ($summary->impressions ?? 0);
        $clicks = (float) ($summary->clicks ?? 0);

        return [
            'impressions' => (int) $impressions,
            'clicks' => (int) $clicks,
            'unique_impressions' => (int) ($summary->unique_impressions ?? 0),
            'unique_clicks' => (int) ($summary->unique_clicks ?? 0),
            'conversions' => (int) ($summary->conversions ?? 0),
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
        ];
    }

    private function buildCountryData(array $filters)
    {
        $query = $this->baseQuery($filters);

        return $query
            ->selectRaw("
                COALESCE(stats.country_code, 'N/A') as country_code,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr
            ")
            ->groupBy('stats.country_code')
            ->orderByDesc('impressions')
            ->get();
    }
}
