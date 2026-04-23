<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CampaignReportController extends Controller
{
    /**
     * Display campaign performance reports.
     */
    public function index(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.campaign_id,
                aq_campaigns.name                          as campaign_name,
                aq_campaigns.advertiser_id,
                aq_campaigns.status                        as campaign_status,
                SUM(aq_stats_daily.impressions)            as total_impressions,
                SUM(aq_stats_daily.unique_impressions)     as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                 as total_clicks,
                SUM(aq_stats_daily.unique_clicks)          as total_unique_clicks,
                SUM(aq_stats_daily.conversions)            as total_conversions,
                SUM(aq_stats_daily.revenue)                as total_spend,
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
            ->join('aq_campaigns', 'aq_stats_daily.campaign_id', '=', 'aq_campaigns.id')
            ->whereNotNull('aq_stats_daily.campaign_id')
            ->where('aq_campaigns.advertiser_id', auth()->id())
            ->groupBy(
                'month',
                'aq_stats_daily.campaign_id',
                'aq_campaigns.name',
                'aq_campaigns.advertiser_id',
                'aq_campaigns.status'
            );

        $rows = $query->get();

        // Load advertiser info
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers   = User::whereIn('id', $advertiserIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        $collection = collect($rows->map(fn($r) => (object) $r->toArray()));

        // ── Filters ──────────────────────────────────────────────────────
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser    = $advertisers->get($item->advertiser_id);
                $advName       = $advertiser ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) : '';
                $advEmail      = $advertiser->email ?? '';
                $campaignName  = $item->campaign_name ?? '';
                return stripos($campaignName, $search) !== false
                    || stripos($advName, $search) !== false
                    || stripos($advEmail, $search) !== false;
            });
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
        $activeCampaigns        = $collection->pluck('campaign_id')->unique()->count();

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

        // Attach advertiser names
        foreach ($reports as $row) {
            $advertiser            = $advertisers->get($row->advertiser_id);
            $row->advertiser_name  = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $row->advertiser_email = $advertiser->email ?? 'N/A';
            $row->month_formatted  = Carbon::parse($row->month . '-01')->format('F Y');
        }

        // Dropdown lists for filters
        $allCampaigns    = Campaign::where('advertiser_id', auth()->id())->orderBy('name')->get(['id', 'name', 'advertiser_id']);
        $allAdvertisers  = collect();

        return view('advertiser.campaign-reports.index', compact(
            'reports',
            'totalClicks',
            'totalUniqueImpressions',
            'totalUniqueClicks',
            'totalConversions',
            'totalImpressions',
            'totalSpend',
            'activeCampaigns',
            'allCampaigns',
            'allAdvertisers'
        ));
    }

    /**
     * Export campaign reports to CSV.
     */
    public function export(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.campaign_id,
                aq_campaigns.name                          as campaign_name,
                aq_campaigns.advertiser_id,
                aq_campaigns.status                        as campaign_status,
                SUM(aq_stats_daily.impressions)            as total_impressions,
                SUM(aq_stats_daily.unique_impressions)     as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                 as total_clicks,
                SUM(aq_stats_daily.unique_clicks)          as total_unique_clicks,
                SUM(aq_stats_daily.conversions)            as total_conversions,
                SUM(aq_stats_daily.revenue)                as total_spend,
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
            ->join('aq_campaigns', 'aq_stats_daily.campaign_id', '=', 'aq_campaigns.id')
            ->whereNotNull('aq_stats_daily.campaign_id')
            ->where('aq_campaigns.advertiser_id', auth()->id())
            ->groupBy(
                'month',
                'aq_stats_daily.campaign_id',
                'aq_campaigns.name',
                'aq_campaigns.advertiser_id',
                'aq_campaigns.status'
            );

        $rows          = $query->get();
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers   = User::whereIn('id', $advertiserIds)->with('userProfile')->get()->keyBy('id');
        $collection    = collect($rows->map(fn($r) => (object) $r->toArray()));

        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser   = $advertisers->get($item->advertiser_id);
                $advName      = $advertiser ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? '')) : '';
                return stripos($item->campaign_name ?? '', $search) !== false
                    || stripos($advName, $search) !== false
                    || stripos($advertiser->email ?? '', $search) !== false;
            });
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

        $filename = 'campaign_reports_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        $callback = function () use ($collection) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Campaign Name', 'Impressions', 'Clicks', 'Conversions', 'Spend (EUR)', 'CTR (%)', 'ECPM (EUR)']);
            foreach ($collection as $row) {
                fputcsv($file, [
                    $row->campaign_name,
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





