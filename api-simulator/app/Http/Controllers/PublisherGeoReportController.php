<?php

namespace App\Http\Controllers;

use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublisherGeoReportController extends Controller
{
    private const COUNTRY_NAMES = [
        'AL' => 'Albania', 'AD' => 'Andorra', 'AT' => 'Austria', 'BY' => 'Belarus',
        'BE' => 'Belgium', 'BA' => 'Bosnia and Herzegovina', 'BG' => 'Bulgaria',
        'HR' => 'Croatia', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark',
        'EE' => 'Estonia', 'FI' => 'Finland', 'FR' => 'France', 'DE' => 'Germany',
        'GR' => 'Greece', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IE' => 'Ireland',
        'IT' => 'Italy', 'XK' => 'Kosovo', 'LV' => 'Latvia', 'LI' => 'Liechtenstein',
        'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MT' => 'Malta', 'MD' => 'Moldova',
        'MC' => 'Monaco', 'ME' => 'Montenegro', 'NL' => 'Netherlands',
        'MK' => 'North Macedonia', 'NO' => 'Norway', 'PL' => 'Poland', 'PT' => 'Portugal',
        'RO' => 'Romania', 'RU' => 'Russia', 'SM' => 'San Marino', 'RS' => 'Serbia',
        'SK' => 'Slovakia', 'SI' => 'Slovenia', 'ES' => 'Spain', 'SE' => 'Sweden',
        'CH' => 'Switzerland', 'TR' => 'Turkey', 'UA' => 'Ukraine', 'GB' => 'United Kingdom',
        'VA' => 'Vatican City', 'US' => 'United States', 'CA' => 'Canada', 'MX' => 'Mexico',
        'BR' => 'Brazil', 'AR' => 'Argentina', 'AU' => 'Australia', 'NZ' => 'New Zealand',
        'JP' => 'Japan', 'CN' => 'China', 'IN' => 'India', 'KR' => 'South Korea',
        'SG' => 'Singapore', 'AE' => 'United Arab Emirates', 'SA' => 'Saudi Arabia',
        'ZA' => 'South Africa', 'EG' => 'Egypt', 'NG' => 'Nigeria', 'KE' => 'Kenya',
    ];

    private const COUNTRY_COORDS = [
        'AL' => ['lat' => 41.1533, 'lng' => 20.1683], 'AT' => ['lat' => 47.5162, 'lng' => 14.5501],
        'BA' => ['lat' => 43.9159, 'lng' => 17.6791], 'BG' => ['lat' => 42.7339, 'lng' => 25.4858],
        'HR' => ['lat' => 45.1,    'lng' => 15.2],    'CZ' => ['lat' => 49.8175, 'lng' => 15.473],
        'DK' => ['lat' => 56.2639, 'lng' => 9.5018],  'FI' => ['lat' => 61.9241, 'lng' => 25.7482],
        'FR' => ['lat' => 46.2276, 'lng' => 2.2137],  'DE' => ['lat' => 51.1657, 'lng' => 10.4515],
        'GR' => ['lat' => 39.0742, 'lng' => 21.8243], 'HU' => ['lat' => 47.1625, 'lng' => 19.5033],
        'IT' => ['lat' => 41.8719, 'lng' => 12.5674], 'XK' => ['lat' => 42.6026, 'lng' => 20.903],
        'ME' => ['lat' => 42.7087, 'lng' => 19.3744], 'MK' => ['lat' => 41.5124, 'lng' => 21.4473],
        'NL' => ['lat' => 52.1326, 'lng' => 5.2913],  'PL' => ['lat' => 51.9194, 'lng' => 19.1451],
        'PT' => ['lat' => 39.3999, 'lng' => -8.2245], 'RO' => ['lat' => 45.9432, 'lng' => 24.9668],
        'RS' => ['lat' => 44.0165, 'lng' => 21.0059], 'SK' => ['lat' => 48.669,  'lng' => 19.699],
        'SI' => ['lat' => 46.1512, 'lng' => 14.9955], 'ES' => ['lat' => 40.4637, 'lng' => -3.7492],
        'SE' => ['lat' => 60.1282, 'lng' => 18.6435], 'CH' => ['lat' => 46.8182, 'lng' => 8.2275],
        'TR' => ['lat' => 38.9637, 'lng' => 35.2433], 'GB' => ['lat' => 55.3781, 'lng' => -3.436],
        'US' => ['lat' => 37.0902, 'lng' => -95.7129],'CA' => ['lat' => 56.1304, 'lng' => -106.3468],
        'AU' => ['lat' => -25.2744,'lng' => 133.7751], 'JP' => ['lat' => 36.2048, 'lng' => 138.2529],
        'CN' => ['lat' => 35.8617, 'lng' => 104.1954], 'IN' => ['lat' => 20.5937, 'lng' => 78.9629],
        'BR' => ['lat' => -14.235, 'lng' => -51.9253],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search'     => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $summary     = $this->buildSummary($filters);
        $tableData   = $this->buildTableData($filters);
        $countryTotals = $this->buildCountryTotals($filters);

        $mapData = $countryTotals->map(function ($row) {
            $code   = $row->country_code ?? 'N/A';
            $coords = self::COUNTRY_COORDS[$code] ?? null;

            return [
                'country_code' => $code,
                'country_name' => self::COUNTRY_NAMES[$code] ?? $code,
                'impressions'  => (int) $row->impressions,
                'clicks'       => (int) $row->clicks,
                'earnings'     => (float) $row->earnings,
                'lat'          => $coords['lat'] ?? null,
                'lng'          => $coords['lng'] ?? null,
            ];
        })->filter(fn($item) => $item['lat'] !== null)->values();

        return view('publisher.reports.geo', [
            'summary'      => $summary,
            'tableData'    => $tableData,
            'mapData'      => $mapData,
            'countryNames' => self::COUNTRY_NAMES,
            'defaults'     => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date'   => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters  = $request->validate([
            'search'     => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ]);
        $tableData = $this->buildTableData($filters);
        $filename  = 'publisher_geo_report_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($tableData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Country', 'Country Code', 'Impressions', 'Clicks', 'Earnings']);

            foreach ($tableData as $row) {
                fputcsv($file, [
                    $row->date,
                    self::COUNTRY_NAMES[$row->country_code] ?? $row->country_code,
                    $row->country_code,
                    (int) $row->impressions,
                    (int) $row->clicks,
                    number_format((float) $row->earnings, 4, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function baseQuery(array $filters)
    {
        $query = DB::table('aq_stats_daily as stats')
            ->where('stats.publisher_id', Auth::id());

        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate   = $filters['end_date']   ?? now()->toDateString();
        $query->whereBetween('stats.date', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $search = strtoupper(trim((string) $filters['search']));
            $query->where('stats.country_code', 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function buildSummary(array $filters): array
    {
        $row = $this->baseQuery($filters)
            ->selectRaw('
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings
            ')
            ->first();

        return [
            'impressions' => (int) ($row->impressions ?? 0),
            'clicks'      => (int) ($row->clicks ?? 0),
            'earnings'    => (float) ($row->earnings ?? 0),
        ];
    }

    private function buildTableData(array $filters)
    {
        return $this->baseQuery($filters)
            ->selectRaw("
                stats.date,
                COALESCE(stats.country_code, 'N/A') as country_code,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings
            ")
            ->groupBy('stats.date', 'stats.country_code')
            ->orderByDesc('stats.date')
            ->orderByDesc('impressions')
            ->get();
    }

    private function buildCountryTotals(array $filters)
    {
        return $this->baseQuery($filters)
            ->selectRaw("
                COALESCE(stats.country_code, 'N/A') as country_code,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.publisher_earnings), 0) as earnings
            ")
            ->groupBy('stats.country_code')
            ->orderByDesc('impressions')
            ->get();
    }
}
