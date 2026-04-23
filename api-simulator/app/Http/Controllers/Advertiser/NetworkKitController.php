<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NetworkKitController extends Controller
{
    private const COUNTRY_NAMES = [
        'AL' => 'Albania',
        'XK' => 'Kosovo',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'CA' => 'Canada',
        'AU' => 'Australia',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'device' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:10'],
        ]);

        $summary = $this->buildSummary($filters, $request->user()->id);
        $rows = $this->buildRows($filters, $request->user()->id);

        return view('advertiser.network.network-kit.index', [
            'summary' => $summary,
            'rows' => $rows,
            'countryNames' => self::COUNTRY_NAMES,
            'filterOptions' => $this->filterOptions($request->user()->id),
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    private function baseQuery(array $filters, int $advertiserId)
    {
        $query = DB::table('aq_stats_daily as stats')
            ->where('stats.advertiser_id', $advertiserId)
            ->whereBetween('stats.date', [
                $filters['start_date'] ?? now()->subDays(30)->toDateString(),
                $filters['end_date'] ?? now()->toDateString(),
            ]);

        if (! empty($filters['device']) && Schema::hasColumn('aq_stats_daily', 'device_type')) {
            $query->where('stats.device_type', strtolower($filters['device']));
        }

        if (! empty($filters['country']) && Schema::hasColumn('aq_stats_daily', 'country_code')) {
            $query->where('stats.country_code', strtoupper($filters['country']));
        }

        return $query;
    }

    private function buildSummary(array $filters, int $advertiserId): array
    {
        $summary = $this->baseQuery($filters, $advertiserId)
            ->selectRaw('
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.revenue), 0) as revenue
            ')
            ->first();

        $impressions = (float) ($summary->impressions ?? 0);
        $clicks = (float) ($summary->clicks ?? 0);
        $revenue = (float) ($summary->revenue ?? 0);

        return [
            'impressions' => (int) $impressions,
            'clicks' => (int) $clicks,
            'revenue' => $revenue,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
            'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 4) : 0,
        ];
    }

    private function buildRows(array $filters, int $advertiserId)
    {
        return $this->baseQuery($filters, $advertiserId)
            ->leftJoin('aq_zones as zones', 'stats.zone_id', '=', 'zones.id')
            ->leftJoin('aq_ad_sizes as sizes', 'zones.size_id', '=', 'sizes.id')
            ->leftJoin('aq_campaigns as campaigns', 'stats.campaign_id', '=', 'campaigns.id')
            ->selectRaw("
                COALESCE(stats.country_code, 'N/A') as country_code,
                COALESCE(sizes.name, 'N/A') as ad_size,
                COALESCE(campaigns.campaign_type, 'N/A') as type,
                COALESCE(SUM(stats.impressions), 0) as impressions,
                COALESCE(SUM(stats.clicks), 0) as clicks,
                COALESCE(SUM(stats.revenue), 0) as revenue,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.clicks) / SUM(stats.impressions)) * 100, 2) ELSE 0 END as ctr,
                CASE WHEN SUM(stats.impressions) > 0 THEN ROUND((SUM(stats.revenue) / SUM(stats.impressions)) * 1000, 4) ELSE 0 END as ecpm
            ")
            ->groupBy('stats.country_code', 'sizes.name', 'sizes.width', 'sizes.height', 'campaigns.campaign_type')
            ->orderByDesc('impressions')
            ->get();
    }

    private function filterOptions(int $advertiserId): array
    {
        $countries = DB::table('aq_stats_daily')
            ->where('advertiser_id', $advertiserId)
            ->whereNotNull('country_code')
            ->distinct()
            ->orderBy('country_code')
            ->pluck('country_code')
            ->mapWithKeys(fn ($code) => [$code => self::COUNTRY_NAMES[$code] ?? $code])
            ->toArray();

        return [
            'devices' => ['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'],
            'countries' => $countries,
        ];
    }
}
