<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupSettingsReportController extends Controller
{
    private const METRICS = [
        'requests' => 'Request',
        'impressions' => 'Impression',
        'clicks' => 'Clicks',
        'conversions' => 'Conversion',
        'spend' => 'Spend',
        'ctr' => 'CTR',
        'ecpm' => 'ECPM',
        'fill_rate' => 'Fill Rate',
    ];

    private const DIMENSIONS = [
        'campaign' => 'Campaign',
        'creative' => 'Creative',
        'country' => 'Country',
        'ad_size' => 'Ad Size',
        'site_url' => 'Site URL',
        'domain' => 'Domain',
        'referrer' => 'Referrer',
        'revenue_type' => 'Revenue Type',
        'date' => 'Date',
    ];

    private const DISPLAY_BY = [
        'cumulative' => 'Cumulative',
        'hour' => 'Hour',
        'month' => 'Month',
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->buildRows($filters);

        return view('advertiser.group-settings-reports.index', [
            'rows' => $rows,
            'filters' => $filters,
            'summary' => $this->summary($filters),
            'metricOptions' => self::METRICS,
            'dimensionOptions' => self::DIMENSIONS,
            'displayOptions' => self::DISPLAY_BY,
            'filterOptions' => $this->filterOptions(),
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'device_type' => ['nullable', 'in:all,website,mobile,tablet'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'metrics' => ['nullable', 'array'],
            'metrics.*' => ['string', 'in:' . implode(',', array_keys(self::METRICS))],
            'campaigns' => ['nullable', 'array'],
            'campaigns.*' => ['integer'],
            'creatives' => ['nullable', 'array'],
            'creatives.*' => ['integer'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'max:5'],
            'ad_sizes' => ['nullable', 'array'],
            'ad_sizes.*' => ['string', 'max:50'],
            'revenue_types' => ['nullable', 'array'],
            'revenue_types.*' => ['string', 'max:50'],
            'group_by' => ['nullable', 'array'],
            'group_by.*' => ['string', 'in:' . implode(',', array_keys(self::DIMENSIONS))],
            'display_by' => ['nullable', 'in:' . implode(',', array_keys(self::DISPLAY_BY))],
        ]);

        return [
            'start_date' => $validated['start_date'] ?? now()->subDays(30)->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->toDateString(),
            'device_type' => $validated['device_type'] ?? 'all',
            'timezone' => $validated['timezone'] ?? config('app.timezone', 'UTC'),
            'metrics' => array_values($validated['metrics'] ?? array_keys(self::METRICS)),
            'campaigns' => array_values($validated['campaigns'] ?? []),
            'creatives' => array_values($validated['creatives'] ?? []),
            'countries' => array_values(array_map('strtoupper', $validated['countries'] ?? [])),
            'ad_sizes' => array_values($validated['ad_sizes'] ?? []),
            'revenue_types' => array_values($validated['revenue_types'] ?? []),
            'group_by' => array_values($validated['group_by'] ?? ['campaign']),
            'display_by' => $validated['display_by'] ?? 'cumulative',
        ];
    }

    private function baseQuery(array $filters)
    {
        $referrerSubquery = DB::table('aq_url_ad_reports')
            ->selectRaw("ad_id, campaign_id, zone_id, DATE(created_at) as report_date, MIN(COALESCE(NULLIF(referrer_url, ''), 'Direct')) as referrer_url")
            ->whereNotNull('created_at')
            ->groupBy('ad_id', 'campaign_id', 'zone_id', DB::raw('DATE(created_at)'));

        $query = DB::table('aq_stats_daily as stats')
            ->join('aq_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_ads as ads', 'stats.ad_id', '=', 'ads.id')
            ->leftJoin('aq_zones as zones', 'stats.zone_id', '=', 'zones.id')
            ->leftJoin('aq_sites as sites', 'stats.site_id', '=', 'sites.id')
            ->leftJoin('aq_ad_sizes as sizes', 'zones.size_id', '=', 'sizes.id')
            ->leftJoinSub($referrerSubquery, 'refs', function ($join) {
                $join->on('refs.ad_id', '=', 'stats.ad_id')
                    ->on('refs.campaign_id', '=', 'stats.campaign_id')
                    ->on('refs.zone_id', '=', 'stats.zone_id')
                    ->whereRaw('refs.report_date = DATE(stats.date)');
            })
            ->where('campaigns.advertiser_id', auth()->id())
            ->whereBetween('stats.date', [$filters['start_date'], $filters['end_date']]);

        if ($filters['device_type'] !== 'all') {
            $query->where('stats.device_type', $filters['device_type'] === 'website' ? 'desktop' : $filters['device_type']);
        }

        if ($filters['campaigns'] !== []) {
            $query->whereIn('campaigns.id', $filters['campaigns']);
        }

        if ($filters['creatives'] !== []) {
            $query->whereIn('ads.id', $filters['creatives']);
        }

        if ($filters['countries'] !== []) {
            $query->whereIn('stats.country_code', $filters['countries']);
        }

        if ($filters['ad_sizes'] !== []) {
            $query->whereIn(DB::raw($this->adSizeExpression()), $filters['ad_sizes']);
        }

        if ($filters['revenue_types'] !== []) {
            $query->whereIn('campaigns.campaign_type', $filters['revenue_types']);
        }

        return $query;
    }

    private function buildRows(array $filters)
    {
        [$selects, $groups] = $this->dimensionSelects($filters);

        $query = $this->baseQuery($filters)
            ->selectRaw(implode(",\n", array_merge($selects, [
                'COALESCE(SUM(stats.impressions), 0) as requests',
                'COALESCE(SUM(stats.impressions), 0) as impressions',
                'COALESCE(SUM(stats.clicks), 0) as clicks',
                'COALESCE(SUM(stats.conversions), 0) as conversions',
                'COALESCE(SUM(stats.revenue), 0) as spend',
                'CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) * 1.0 / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr',
                'CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) * 1.0 / SUM(stats.impressions)) * 1000, 2) ELSE 0 END as ecpm',
                'ROUND(AVG(COALESCE(stats.fill_rate, 0)), 2) as fill_rate',
            ])));

        foreach ($groups as $group) {
            $query->groupBy(DB::raw($group));
        }

        $rows = $query->orderByDesc('impressions')
            ->paginate(25)
            ->withQueryString();

        $rows->getCollection()->transform(fn ($row) => $this->decorateRow($row));

        return $rows;
    }

    private function dimensionSelects(array $filters): array
    {
        $selected = $filters['group_by'];

        if ($filters['display_by'] !== 'cumulative' && !in_array('date', $selected, true)) {
            $selected[] = 'date';
        }

        $selects = [];
        $groups = [];

        foreach ($selected as $dimension) {
            $definition = $this->dimensionDefinition($dimension, $filters['display_by']);
            $selects[] = $definition['select'];
            $groups[] = $definition['group'];
        }

        return [$selects, $groups];
    }

    private function dimensionDefinition(string $dimension, string $displayBy): array
    {
        return match ($dimension) {
            'campaign' => ['select' => "COALESCE(campaigns.name, 'N/A') as campaign", 'group' => "COALESCE(campaigns.name, 'N/A')"],
            'creative' => ['select' => "COALESCE(ads.name, 'N/A') as creative", 'group' => "COALESCE(ads.name, 'N/A')"],
            'country' => ['select' => "COALESCE(stats.country_code, 'N/A') as country", 'group' => "COALESCE(stats.country_code, 'N/A')"],
            'ad_size' => ['select' => $this->adSizeExpression() . ' as ad_size', 'group' => $this->adSizeExpression()],
            'site_url' => ['select' => "COALESCE(sites.domain, 'N/A') as site_url", 'group' => "COALESCE(sites.domain, 'N/A')"],
            'domain' => ['select' => "COALESCE(sites.domain, 'N/A') as domain", 'group' => "COALESCE(sites.domain, 'N/A')"],
            'referrer' => ['select' => "COALESCE(refs.referrer_url, 'Direct') as referrer", 'group' => "COALESCE(refs.referrer_url, 'Direct')"],
            'revenue_type' => ['select' => "UPPER(COALESCE(campaigns.campaign_type, 'n/a')) as revenue_type", 'group' => "UPPER(COALESCE(campaigns.campaign_type, 'n/a'))"],
            'date' => $this->dateDimension($displayBy),
            default => ['select' => "COALESCE(campaigns.name, 'N/A') as campaign", 'group' => "COALESCE(campaigns.name, 'N/A')"],
        };
    }

    private function dateDimension(string $displayBy): array
    {
        $driver = DB::connection()->getDriverName();

        if ($displayBy === 'month') {
            $monthExpression = $driver === 'sqlite'
                ? "strftime('%Y-%m', stats.date)"
                : "DATE_FORMAT(stats.date, '%Y-%m')";

            return ['select' => $monthExpression . ' as date', 'group' => $monthExpression];
        }

        if ($displayBy === 'hour') {
            $hourExpression = $driver === 'sqlite'
                ? "stats.date || ' 00:00'"
                : "CONCAT(stats.date, ' 00:00')";

            return ['select' => $hourExpression . ' as date', 'group' => $hourExpression];
        }

        return ['select' => 'DATE(stats.date) as date', 'group' => 'DATE(stats.date)'];
    }

    private function summary(array $filters): array
    {
        $summary = $this->baseQuery($filters)
            ->selectRaw('
                COALESCE(SUM(stats.impressions), 0) as requests,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.conversions), 0) as conversions,
                COALESCE(SUM(stats.revenue), 0) as spend,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) * 1.0 / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) * 1.0 / SUM(stats.impressions)) * 1000, 2) ELSE 0 END as ecpm,
                ROUND(AVG(COALESCE(stats.fill_rate, 0)), 2) as fill_rate
            ')
            ->first();

        return [
            'requests' => (int) ($summary->requests ?? 0),
            'impressions' => (int) ($summary->impressions ?? 0),
            'clicks' => (int) ($summary->clicks ?? 0),
            'conversions' => (int) ($summary->conversions ?? 0),
            'spend' => (float) ($summary->spend ?? 0),
            'ctr' => (float) ($summary->ctr ?? 0),
            'ecpm' => (float) ($summary->ecpm ?? 0),
            'fill_rate' => (float) ($summary->fill_rate ?? 0),
        ];
    }

    private function filterOptions(): array
    {
        $advertiserId = auth()->id();

        $campaigns = DB::table('aq_campaigns')
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_type']);

        $creatives = DB::table('aq_ads as ads')
            ->join('aq_campaigns as campaigns', 'ads.campaign_id', '=', 'campaigns.id')
            ->where('campaigns.advertiser_id', $advertiserId)
            ->where('ads.is_deleted', false)
            ->orderBy('ads.name')
            ->get(['ads.id', 'ads.name']);

        $countries = DB::table('aq_stats_daily as stats')
            ->join('aq_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
            ->where('campaigns.advertiser_id', $advertiserId)
            ->whereNotNull('stats.country_code')
            ->distinct()
            ->orderBy('stats.country_code')
            ->pluck('stats.country_code');

        $adSizes = DB::table('aq_stats_daily as stats')
            ->join('aq_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
            ->leftJoin('aq_zones as zones', 'stats.zone_id', '=', 'zones.id')
            ->leftJoin('aq_ad_sizes as sizes', 'zones.size_id', '=', 'sizes.id')
            ->where('campaigns.advertiser_id', $advertiserId)
            ->selectRaw('DISTINCT ' . $this->adSizeExpression() . ' as ad_size')
            ->orderBy('ad_size')
            ->pluck('ad_size');

        return [
            'campaigns' => $campaigns,
            'creatives' => $creatives,
            'countries' => $countries,
            'ad_sizes' => $adSizes,
            'revenue_types' => $campaigns->pluck('campaign_type')->filter()->unique()->values(),
        ];
    }

    private function decorateRow($row)
    {
        if (isset($row->site_url) && $row->site_url !== 'N/A' && !Str::startsWith($row->site_url, ['http://', 'https://'])) {
            $row->site_url = 'https://' . $row->site_url;
        }

        return $row;
    }

    private function adSizeExpression(): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "COALESCE(CAST(sizes.width AS TEXT) || 'x' || CAST(sizes.height AS TEXT), zones.size_key, 'N/A')";
        }

        return "COALESCE(CONCAT(sizes.width, 'x', sizes.height), zones.size_key, 'N/A')";
    }
}
