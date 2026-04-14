<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdSize;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformReportController extends Controller
{
    private const DEFAULT_METRICS = ['impressions', 'clicks', 'conversions', 'revenue', 'profit', 'ctr'];

    private const DEFAULT_DIMENSIONS = ['date', 'advertiser', 'campaign'];

    private const INTERVALS = [
        'cumulative' => 'Cumulative',
        'day' => 'Day',
        'month' => 'Month',
    ];

    private const DEVICE_TYPES = [
        'desktop' => 'Desktop',
        'mobile' => 'Mobile',
        'tablet' => 'Tablet',
    ];

    private const REVENUE_TYPES = [
        'cpm' => 'CPM',
        'cpc' => 'CPC',
        'cpa' => 'CPA',
        'cpv' => 'CPV',
        'cpv_ctw' => 'CPV CTW',
    ];

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        [$reports, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $interval] = $this->buildReport($filters, true);

        return view('admin.platform-reports.index', [
            'reports' => $reports,
            'summary' => $summary,
            'tableColumns' => $tableColumns,
            'selectedMetrics' => $selectedMetrics,
            'selectedDimensions' => $selectedDimensions,
            'interval' => $interval,
            'availableIntervals' => self::INTERVALS,
            'availableMetrics' => $this->metricDefinitions(),
            'availableDimensions' => $this->dimensionDefinitions(),
            'availableDeviceTypes' => self::DEVICE_TYPES,
            'availableRevenueTypes' => self::REVENUE_TYPES,
            'advertisers' => $this->advertisers(),
            'publishers' => $this->publishers(),
            'campaigns' => $this->campaigns(),
            'creatives' => $this->creatives(),
            'sites' => $this->sites(),
            'adSizes' => $this->adSizes(),
            'countries' => $this->countries(),
            'defaults' => [
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);

        [$rows, , $tableColumns] = $this->buildReport($filters, false);

        $filename = 'platform_reports_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows, $tableColumns) {
            $file = fopen('php://output', 'w');

            fputcsv($file, array_column($tableColumns, 'label'));

            foreach ($rows as $row) {
                $csvRow = [];

                foreach ($tableColumns as $column) {
                    $csvRow[] = $this->formatValueForExport($row->{$column['key']} ?? null, $column);
                }

                fputcsv($file, $csvRow);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildReport(array $filters, bool $paginated): array
    {
        $selectedMetrics = $this->normalizeSelections(
            $filters['metrics'] ?? [],
            array_keys($this->metricDefinitions()),
            self::DEFAULT_METRICS
        );

        $selectedDimensions = $this->normalizeSelections(
            $filters['dimensions'] ?? [],
            array_keys($this->dimensionDefinitions()),
            self::DEFAULT_DIMENSIONS
        );

        $interval = $filters['interval'] ?? 'day';
        if ($interval === 'cumulative' && in_array('date', $selectedDimensions, true)) {
            $interval = 'day';
        }

        $baseQuery = $this->baseQuery($filters);
        $summary = $this->summary((clone $baseQuery));

        $groupedQuery = $this->groupedQuery((clone $baseQuery), $selectedDimensions, $selectedMetrics, $interval);
        $tableColumns = $this->tableColumns($selectedDimensions, $selectedMetrics, $interval);

        if ($paginated) {
            $reports = $groupedQuery
                ->paginate(20)
                ->withQueryString();

            $reports->setCollection($this->decorateRows($reports->getCollection(), $tableColumns));

            return [$reports, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $interval];
        }

        $rows = $this->decorateRows($groupedQuery->get(), $tableColumns);

        return [$rows, $summary, $tableColumns, $selectedMetrics, $selectedDimensions, $interval];
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'interval' => ['nullable', 'in:' . implode(',', array_keys(self::INTERVALS))],
            'device_type' => ['nullable', 'in:' . implode(',', array_keys(self::DEVICE_TYPES))],
            'advertiser_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'creative_id' => ['nullable', 'integer'],
            'publisher_id' => ['nullable', 'integer'],
            'site_id' => ['nullable', 'integer'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'ad_size_id' => ['nullable', 'integer'],
            'revenue_type' => ['nullable', 'in:' . implode(',', array_keys(self::REVENUE_TYPES))],
            'adblock' => ['nullable', 'in:any,detected,clean'],
            'metrics' => ['nullable', 'array'],
            'metrics.*' => ['string'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.*' => ['string'],
        ]);
    }

    private function baseQuery(array $filters)
    {
        $query = DB::table('aq_stats_daily as stats')
            ->leftJoin('aq_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_ads as ads', 'stats.ad_id', '=', 'ads.id')
            ->leftJoin('aq_users as advertisers', 'stats.advertiser_id', '=', 'advertisers.id')
            ->leftJoin('aq_user_profiles as advertiser_profiles', 'advertisers.id', '=', 'advertiser_profiles.user_id')
            ->leftJoin('aq_users as publishers', 'stats.publisher_id', '=', 'publishers.id')
            ->leftJoin('aq_user_profiles as publisher_profiles', 'publishers.id', '=', 'publisher_profiles.user_id')
            ->leftJoin('aq_sites as sites', 'stats.site_id', '=', 'sites.id')
            ->leftJoin('aq_zones as zones', 'stats.zone_id', '=', 'zones.id')
            ->leftJoin('aq_ad_sizes as sizes', 'zones.size_id', '=', 'sizes.id');

        $startDate = $filters['start_date'] ?? now()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $query->whereBetween('stats.date', [$startDate, $endDate]);

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($inner) use ($search) {
                $inner
                    ->orWhere('campaigns.name', 'like', '%' . $search . '%')
                    ->orWhere('ads.name', 'like', '%' . $search . '%')
                    ->orWhere('sites.name', 'like', '%' . $search . '%')
                    ->orWhere('stats.country_code', 'like', '%' . strtoupper($search) . '%')
                    ->orWhere('advertisers.email', 'like', '%' . $search . '%')
                    ->orWhere('publishers.email', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("TRIM(CONCAT(COALESCE(advertiser_profiles.first_name, ''), ' ', COALESCE(advertiser_profiles.last_name, '')))"), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("TRIM(CONCAT(COALESCE(publisher_profiles.first_name, ''), ' ', COALESCE(publisher_profiles.last_name, '')))"), 'like', '%' . $search . '%');
            });
        }

        foreach ([
            'advertiser_id' => 'stats.advertiser_id',
            'campaign_id' => 'stats.campaign_id',
            'creative_id' => 'stats.ad_id',
            'publisher_id' => 'stats.publisher_id',
            'site_id' => 'stats.site_id',
            'country_code' => 'stats.country_code',
            'revenue_type' => 'campaigns.campaign_type',
        ] as $filterKey => $column) {
            if (! empty($filters[$filterKey])) {
                $query->where($column, $filterKey === 'country_code'
                    ? strtoupper((string) $filters[$filterKey])
                    : $filters[$filterKey]);
            }
        }

        if (! empty($filters['device_type'])) {
            $query->where('stats.device_type', $filters['device_type']);
        }

        if (! empty($filters['ad_size_id'])) {
            $query->where('zones.size_id', $filters['ad_size_id']);
        }

        if (($filters['adblock'] ?? 'any') === 'detected') {
            $query->where('stats.adblock_detected', '>', 0);
        } elseif (($filters['adblock'] ?? 'any') === 'clean') {
            $query->where(function ($inner) {
                $inner->whereNull('stats.adblock_detected')
                    ->orWhere('stats.adblock_detected', 0);
            });
        }

        return $query;
    }

    private function groupedQuery($query, array $selectedDimensions, array $selectedMetrics, string $interval)
    {
        $dimensionDefinitions = $this->dimensionDefinitions();
        $metricDefinitions = $this->metricDefinitions();

        foreach ($selectedDimensions as $dimension) {
            if ($dimension === 'date') {
                $period = $this->periodDefinition($interval);
                $query->selectRaw($period['select']);
                $query->groupBy(DB::raw($period['group']));
                continue;
            }

            $definition = $dimensionDefinitions[$dimension];
            $query->selectRaw($definition['select']);
            $query->groupBy(DB::raw($definition['group']));
        }

        foreach ($selectedMetrics as $metric) {
            $query->selectRaw($metricDefinitions[$metric]['select']);
        }

        if (in_array('date', $selectedDimensions, true)) {
            $period = $this->periodDefinition($interval);
            $query->orderBy(DB::raw($period['group']), 'desc');
        } else {
            $query->orderByRaw('MAX(stats.date) DESC');
        }

        foreach ($selectedDimensions as $dimension) {
            if ($dimension === 'date') {
                continue;
            }

            $query->orderBy($this->dimensionDefinitions()[$dimension]['key']);
        }

        return $query;
    }

    private function summary($query): array
    {
        $summary = $query
            ->selectRaw('
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.revenue), 0) as revenue,
                COALESCE(SUM(stats.publisher_earnings), 0) as publisher_earnings,
                COALESCE(SUM(stats.revenue - stats.publisher_earnings), 0) as profit,
                COUNT(DISTINCT stats.advertiser_id) as advertisers,
                COUNT(DISTINCT stats.publisher_id) as publishers
            ')
            ->first();

        $impressions = (float) ($summary->impressions ?? 0);
        $clicks = (float) ($summary->clicks ?? 0);
        $revenue = (float) ($summary->revenue ?? 0);
        $publisherEarnings = (float) ($summary->publisher_earnings ?? 0);

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => (float) ($summary->conversions ?? 0),
            'revenue' => $revenue,
            'publisher_earnings' => $publisherEarnings,
            'profit' => (float) ($summary->profit ?? 0),
            'advertisers' => (int) ($summary->advertisers ?? 0),
            'publishers' => (int) ($summary->publishers ?? 0),
            'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
            'ecpm' => $impressions > 0 ? ($revenue / $impressions) * 1000 : 0,
        ];
    }

    private function decorateRows(Collection $rows, array $tableColumns): Collection
    {
        return $rows->map(function ($row) use ($tableColumns) {
            foreach ($tableColumns as $column) {
                $key = $column['key'];
                $row->{$key . '_formatted'} = $this->formatValueForDisplay($row->{$key} ?? null, $column);
            }

            return $row;
        });
    }

    private function tableColumns(array $selectedDimensions, array $selectedMetrics, string $interval): array
    {
        $columns = [];

        foreach ($selectedDimensions as $dimension) {
            if ($dimension === 'date') {
                $columns[] = [
                    'key' => 'report_period',
                    'label' => $interval === 'month' ? 'Month' : 'Date',
                    'type' => 'text',
                ];
                continue;
            }

            $definition = $this->dimensionDefinitions()[$dimension];
            $columns[] = [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'type' => 'text',
            ];
        }

        foreach ($selectedMetrics as $metric) {
            $definition = $this->metricDefinitions()[$metric];
            $columns[] = [
                'key' => $metric,
                'label' => $definition['label'],
                'type' => $definition['type'],
            ];
        }

        return $columns;
    }

    private function formatValueForDisplay(mixed $value, array $column): string
    {
        return match ($column['type']) {
            'currency' => 'EUR ' . number_format((float) $value, 2),
            'percent' => number_format((float) $value, 2) . '%',
            'number' => number_format((float) $value),
            default => (string) ($value ?? '—'),
        };
    }

    private function formatValueForExport(mixed $value, array $column): string
    {
        if ($value === null) {
            return '';
        }

        return match ($column['type']) {
            'currency', 'percent' => number_format((float) $value, 2, '.', ''),
            'number' => number_format((float) $value, 0, '.', ''),
            default => (string) $value,
        };
    }

    private function normalizeSelections(array $values, array $allowed, array $fallback): array
    {
        $selected = array_values(array_intersect($values, $allowed));

        return $selected !== [] ? $selected : $fallback;
    }

    private function periodDefinition(string $interval): array
    {
        if ($interval === 'month') {
            return [
                'select' => "DATE_FORMAT(stats.date, '%Y-%m') as report_period",
                'group' => "DATE_FORMAT(stats.date, '%Y-%m')",
            ];
        }

        return [
            'select' => "DATE_FORMAT(stats.date, '%Y-%m-%d') as report_period",
            'group' => 'stats.date',
        ];
    }

    private function metricDefinitions(): array
    {
        return [
            'impressions' => [
                'label' => 'Impressions',
                'select' => 'COALESCE(SUM(stats.impressions), 0) as impressions',
                'type' => 'number',
            ],
            'unique_impressions' => [
                'label' => 'Unique Impressions',
                'select' => 'COALESCE(SUM(stats.unique_impressions), 0) as unique_impressions',
                'type' => 'number',
            ],
            'clicks' => [
                'label' => 'Clicks',
                'select' => 'COALESCE(SUM(stats.clicks), 0) as clicks',
                'type' => 'number',
            ],
            'unique_clicks' => [
                'label' => 'Unique Clicks',
                'select' => 'COALESCE(SUM(stats.unique_clicks), 0) as unique_clicks',
                'type' => 'number',
            ],
            'conversions' => [
                'label' => 'Conversions',
                'select' => 'COALESCE(SUM(stats.conversions), 0) as conversions',
                'type' => 'number',
            ],
            'revenue' => [
                'label' => 'Revenue',
                'select' => 'COALESCE(SUM(stats.revenue), 0) as revenue',
                'type' => 'currency',
            ],
            'publisher_earnings' => [
                'label' => 'Publisher Earnings',
                'select' => 'COALESCE(SUM(stats.publisher_earnings), 0) as publisher_earnings',
                'type' => 'currency',
            ],
            'profit' => [
                'label' => 'Profit',
                'select' => 'COALESCE(SUM(stats.revenue - stats.publisher_earnings), 0) as profit',
                'type' => 'currency',
            ],
            'ctr' => [
                'label' => 'CTR',
                'select' => 'CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr',
                'type' => 'percent',
            ],
            'ecpm' => [
                'label' => 'ECPM',
                'select' => 'CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) / SUM(stats.impressions)) * 1000, 2) ELSE 0 END as ecpm',
                'type' => 'currency',
            ],
            'eppm' => [
                'label' => 'EPPM',
                'select' => 'CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.publisher_earnings) / SUM(stats.impressions)) * 1000, 2) ELSE 0 END as eppm',
                'type' => 'currency',
            ],
            'fill_rate' => [
                'label' => 'Fill Rate',
                'select' => 'ROUND(AVG(COALESCE(stats.fill_rate, 0)), 2) as fill_rate',
                'type' => 'percent',
            ],
            'active_advertisers' => [
                'label' => 'Active Advertisers',
                'select' => 'COUNT(DISTINCT stats.advertiser_id) as active_advertisers',
                'type' => 'number',
            ],
            'active_publishers' => [
                'label' => 'Active Publishers',
                'select' => 'COUNT(DISTINCT stats.publisher_id) as active_publishers',
                'type' => 'number',
            ],
        ];
    }

    private function dimensionDefinitions(): array
    {
        return [
            'date' => [
                'key' => 'report_period',
                'label' => 'Date',
            ],
            'advertiser' => [
                'key' => 'advertiser_name',
                'label' => 'Advertiser',
                'select' => "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(advertiser_profiles.first_name, ''), ' ', COALESCE(advertiser_profiles.last_name, ''))), ''), advertisers.email, CONCAT('Advertiser #', stats.advertiser_id)) as advertiser_name",
                'group' => "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(advertiser_profiles.first_name, ''), ' ', COALESCE(advertiser_profiles.last_name, ''))), ''), advertisers.email, CONCAT('Advertiser #', stats.advertiser_id))",
            ],
            'campaign' => [
                'key' => 'campaign_name',
                'label' => 'Campaign',
                'select' => "COALESCE(campaigns.name, CONCAT('Campaign #', stats.campaign_id)) as campaign_name",
                'group' => "COALESCE(campaigns.name, CONCAT('Campaign #', stats.campaign_id))",
            ],
            'creative' => [
                'key' => 'creative_name',
                'label' => 'Creative',
                'select' => "COALESCE(ads.name, CONCAT('Creative #', stats.ad_id)) as creative_name",
                'group' => "COALESCE(ads.name, CONCAT('Creative #', stats.ad_id))",
            ],
            'publisher' => [
                'key' => 'publisher_name',
                'label' => 'Publisher',
                'select' => "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(publisher_profiles.first_name, ''), ' ', COALESCE(publisher_profiles.last_name, ''))), ''), publishers.email, CONCAT('Publisher #', stats.publisher_id)) as publisher_name",
                'group' => "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(publisher_profiles.first_name, ''), ' ', COALESCE(publisher_profiles.last_name, ''))), ''), publishers.email, CONCAT('Publisher #', stats.publisher_id))",
            ],
            'site' => [
                'key' => 'site_name',
                'label' => 'Site',
                'select' => "COALESCE(sites.name, CONCAT('Site #', stats.site_id)) as site_name",
                'group' => "COALESCE(sites.name, CONCAT('Site #', stats.site_id))",
            ],
            'adblock' => [
                'key' => 'adblock_state',
                'label' => 'Adblock',
                'select' => "CASE WHEN COALESCE(stats.adblock_detected, 0) > 0 THEN 'Detected' ELSE 'Clean' END as adblock_state",
                'group' => "CASE WHEN COALESCE(stats.adblock_detected, 0) > 0 THEN 'Detected' ELSE 'Clean' END",
            ],
            'country' => [
                'key' => 'country_code',
                'label' => 'Country',
                'select' => "COALESCE(stats.country_code, 'N/A') as country_code",
                'group' => "COALESCE(stats.country_code, 'N/A')",
            ],
            'ad_size' => [
                'key' => 'ad_size',
                'label' => 'Ad Size',
                'select' => "COALESCE(CONCAT(sizes.width, 'x', sizes.height), zones.size_key, 'N/A') as ad_size",
                'group' => "COALESCE(CONCAT(sizes.width, 'x', sizes.height), zones.size_key, 'N/A')",
            ],
            'revenue_type' => [
                'key' => 'revenue_type_name',
                'label' => 'Revenue Type',
                'select' => "UPPER(COALESCE(campaigns.campaign_type, 'n/a')) as revenue_type_name",
                'group' => "UPPER(COALESCE(campaigns.campaign_type, 'n/a'))",
            ],
            'device' => [
                'key' => 'device_type_name',
                'label' => 'Device',
                'select' => "UPPER(COALESCE(stats.device_type, 'n/a')) as device_type_name",
                'group' => "UPPER(COALESCE(stats.device_type, 'n/a'))",
            ],
        ];
    }

    private function advertisers()
    {
        return User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();
    }

    private function publishers()
    {
        return User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();
    }

    private function campaigns()
    {
        return DB::table('aq_campaigns')
            ->select('id', 'name')
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get();
    }

    private function creatives()
    {
        return Ad::query()
            ->select('id', 'name')
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get();
    }

    private function sites()
    {
        return Site::query()
            ->select('id', 'name')
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get();
    }

    private function adSizes()
    {
        return AdSize::query()
            ->orderBy('width')
            ->orderBy('height')
            ->get(['id', 'width', 'height', 'name']);
    }

    private function countries()
    {
        return DB::table('aq_stats_daily')
            ->whereNotNull('country_code')
            ->distinct()
            ->orderBy('country_code')
            ->pluck('country_code');
    }
}
