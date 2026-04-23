<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CreativeReportController extends Controller
{
    /**
     * Display creative (ad) performance reports.
     */
    public function index(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m')  as month,
                aq_stats_daily.ad_id,
                aq_ads.name                                 as ad_name,
                aq_ads.ad_type,
                aq_ads.status                               as ad_status,
                aq_campaigns.id                             as campaign_id,
                aq_campaigns.name                           as campaign_name,
                aq_campaigns.advertiser_id,
                SUM(aq_stats_daily.impressions)             as total_impressions,
                SUM(aq_stats_daily.unique_impressions)      as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                  as total_clicks,
                SUM(aq_stats_daily.unique_clicks)           as total_unique_clicks,
                SUM(aq_stats_daily.conversions)             as total_conversions,
                SUM(aq_stats_daily.revenue)                 as total_spend,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.clicks) / SUM(aq_stats_daily.impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.revenue) / SUM(aq_stats_daily.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->join('aq_ads', 'aq_stats_daily.ad_id', '=', 'aq_ads.id')
            ->join('aq_campaigns', 'aq_ads.campaign_id', '=', 'aq_campaigns.id')
            ->whereNotNull('aq_stats_daily.ad_id')
            ->where('aq_campaigns.advertiser_id', auth()->id())
            ->groupBy(
                'month',
                'aq_stats_daily.ad_id',
                'aq_ads.name',
                'aq_ads.ad_type',
                'aq_ads.status',
                'aq_campaigns.id',
                'aq_campaigns.name',
                'aq_campaigns.advertiser_id'
            );

        $rows = $query->get();

        // Load advertisers
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers   = User::whereIn('id', $advertiserIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        $collection = collect($rows->map(fn($r) => (object) $r->toArray()));

        // ── Filters ──────────────────────────────────────────────────────
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                $advName    = $advertiser ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) : '';
                return stripos($item->ad_name ?? '', $search) !== false
                    || stripos($item->campaign_name ?? '', $search) !== false
                    || stripos($advName, $search) !== false
                    || stripos($advertiser->email ?? '', $search) !== false;
            });
        }

        if ($adId = $request->get('ad_id')) {
            $collection = $collection->filter(fn($item) => $item->ad_id == $adId);
        }

        if ($campaignId = $request->get('campaign_id')) {
            $collection = $collection->filter(fn($item) => $item->campaign_id == $campaignId);
        }
if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        $collection = $collection->sortByDesc('month')->values();

        // ── Top summary totals ────────────────────────────────────────────
        $totalClicks            = $collection->sum('total_clicks');
        $totalUniqueImpressions = $collection->sum('total_unique_impressions');
        $totalUniqueClicks      = $collection->sum('total_unique_clicks');
        $totalConversions       = $collection->sum('total_conversions');
        $totalImpressions       = $collection->sum('total_impressions');
        $totalSpend             = $collection->sum('total_spend');
        $activeCreatives        = $collection->pluck('ad_id')->unique()->count();

        // ── Pagination ────────────────────────────────────────────────────
        $perPage     = 20;
        $currentPage = $request->get('page', 1);
        $offset      = ($currentPage - 1) * $perPage;

        $paginatedData = $collection->slice($offset, $perPage)->values();

        $reports = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        foreach ($reports as $row) {
            $advertiser            = $advertisers->get($row->advertiser_id);
            $row->advertiser_name  = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $row->advertiser_email = $advertiser->email ?? 'N/A';
            $row->month_formatted  = Carbon::parse($row->month . '-01')->format('F Y');
        }

        // Dropdowns
        $allCampaigns   = Campaign::where('advertiser_id', auth()->id())->orderBy('name')->get(['id', 'name']);
        $allAds         = Ad::where('is_deleted', false)
            ->whereHas('campaign', fn($query) => $query->where('advertiser_id', auth()->id()))
            ->orderBy('name')
            ->get(['id', 'name', 'campaign_id']);
        $allAdvertisers = collect();

        return view('advertiser.creative-reports.index', compact(
            'reports',
            'totalClicks',
            'totalUniqueImpressions',
            'totalUniqueClicks',
            'totalConversions',
            'totalImpressions',
            'totalSpend',
            'activeCreatives',
            'allAds',
            'allCampaigns',
            'allAdvertisers'
        ));
    }

    /**
     * Export creative reports to CSV.
     */
    public function export(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m')  as month,
                aq_stats_daily.ad_id,
                aq_ads.name                                 as ad_name,
                aq_ads.ad_type,
                aq_ads.status                               as ad_status,
                aq_campaigns.id                             as campaign_id,
                aq_campaigns.name                           as campaign_name,
                aq_campaigns.advertiser_id,
                SUM(aq_stats_daily.impressions)             as total_impressions,
                SUM(aq_stats_daily.unique_impressions)      as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                  as total_clicks,
                SUM(aq_stats_daily.unique_clicks)           as total_unique_clicks,
                SUM(aq_stats_daily.conversions)             as total_conversions,
                SUM(aq_stats_daily.revenue)                 as total_spend,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.clicks) / SUM(aq_stats_daily.impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.revenue) / SUM(aq_stats_daily.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->join('aq_ads', 'aq_stats_daily.ad_id', '=', 'aq_ads.id')
            ->join('aq_campaigns', 'aq_ads.campaign_id', '=', 'aq_campaigns.id')
            ->whereNotNull('aq_stats_daily.ad_id')
            ->where('aq_campaigns.advertiser_id', auth()->id())
            ->groupBy(
                'month',
                'aq_stats_daily.ad_id',
                'aq_ads.name',
                'aq_ads.ad_type',
                'aq_ads.status',
                'aq_campaigns.id',
                'aq_campaigns.name',
                'aq_campaigns.advertiser_id'
            );

        $rows          = $query->get();
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers   = User::whereIn('id', $advertiserIds)->with('userProfile')->get()->keyBy('id');
        $collection    = collect($rows->map(fn($r) => (object) $r->toArray()));

        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                $advName    = $advertiser ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) : '';
                return stripos($item->ad_name ?? '', $search) !== false
                    || stripos($item->campaign_name ?? '', $search) !== false
                    || stripos($advName, $search) !== false;
            });
        }

        if ($adId = $request->get('ad_id')) {
            $collection = $collection->filter(fn($item) => $item->ad_id == $adId);
        }

        if ($campaignId = $request->get('campaign_id')) {
            $collection = $collection->filter(fn($item) => $item->campaign_id == $campaignId);
        }
if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        $collection = $collection->sortByDesc('month')->values();

        foreach ($collection as $row) {
            $advertiser            = $advertisers->get($row->advertiser_id);
            $row->advertiser_name  = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $row->advertiser_email = $advertiser->email ?? 'N/A';
            $row->month_formatted  = Carbon::parse($row->month . '-01')->format('F Y');
        }

        $filename = 'creative_reports_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        $callback = function () use ($collection) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Ad Name', 'Ad Type', 'Impressions', 'Clicks', 'Conversions', 'Spend (EUR)', 'CTR (%)', 'ECPM (EUR)']);
            foreach ($collection as $row) {
                fputcsv($file, [
                    $row->ad_name,
                    $row->ad_type ?? '-',
                    number_format($row->total_impressions, 0, '.', ''),
                    number_format($row->total_clicks, 0, '.', ''),
                    number_format($row->total_conversions, 0, '.', ''),
                    number_format($row->total_spend, 2, '.', ''),
                    number_format($row->ctr, 2, '.', ''),
                    number_format($row->ecpm, 2, '.', ''),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}





