<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdvertiserReportController extends Controller
{
    /**
     * Display advertiser performance reports.
     */
    public function index(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.advertiser_id,
                SUM(aq_stats_daily.impressions)        as total_impressions,
                SUM(aq_stats_daily.unique_impressions) as total_unique_impressions,
                SUM(aq_stats_daily.clicks)             as total_clicks,
                SUM(aq_stats_daily.unique_clicks)      as total_unique_clicks,
                SUM(aq_stats_daily.conversions)        as total_conversions,
                SUM(aq_stats_daily.revenue)            as total_spend,
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
            ->join('aq_users', 'aq_stats_daily.advertiser_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->whereNotNull('aq_stats_daily.advertiser_id')
            ->groupBy('month', 'aq_stats_daily.advertiser_id');

        $rows = $query->get();

        // Get all advertiser IDs
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers = User::whereIn('id', $advertiserIds)
            ->with('userProfile')
            ->get()
            ->keyBy('id');

        // Convert to mutable collection
        $collection = collect($rows->map(fn($r) => (object) $r->toArray()));

        // ── Filters ──────────────────────────────────────────────────────
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                if (!$advertiser) return false;
                $name  = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''));
                $email = $advertiser->email ?? '';
                return stripos($name, $search) !== false || stripos($email, $search) !== false;
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $collection = $collection->filter(fn($item) => $item->advertiser_id == $advertiserId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        $collection = $collection->sortByDesc('month')->values();

        // ── Top summary totals ────────────────────────────────────────────
        $totalClicks           = $collection->sum('total_clicks');
        $totalUniqueImpressions = $collection->sum('total_unique_impressions');
        $totalUniqueClicks     = $collection->sum('total_unique_clicks');
        $totalConversions      = $collection->sum('total_conversions');
        $totalImpressions      = $collection->sum('total_impressions');
        $totalSpend            = $collection->sum('total_spend');
        $activeAdvertisers     = $collection->pluck('advertiser_id')->unique()->count();

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
            $advertiser = $advertisers->get($row->advertiser_id);
            $row->advertiser_name  = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $row->advertiser_email  = $advertiser->email ?? 'N/A';
            $row->month_formatted  = Carbon::parse($row->month . '-01')->format('F Y');
        }

        // Dropdown list for filter
        $allAdvertisers = User::where('role', 'advertiser')
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.advertiser-reports.index', compact(
            'reports',
            'totalClicks',
            'totalUniqueImpressions',
            'totalUniqueClicks',
            'totalConversions',
            'totalImpressions',
            'totalSpend',
            'activeAdvertisers',
            'allAdvertisers'
        ));
    }

    /**
     * Export advertiser reports to CSV.
     */
    public function export(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.advertiser_id,
                SUM(aq_stats_daily.impressions)        as total_impressions,
                SUM(aq_stats_daily.unique_impressions) as total_unique_impressions,
                SUM(aq_stats_daily.clicks)             as total_clicks,
                SUM(aq_stats_daily.unique_clicks)      as total_unique_clicks,
                SUM(aq_stats_daily.conversions)        as total_conversions,
                SUM(aq_stats_daily.revenue)            as total_spend,
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
            ->join('aq_users', 'aq_stats_daily.advertiser_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->whereNotNull('aq_stats_daily.advertiser_id')
            ->groupBy('month', 'aq_stats_daily.advertiser_id');

        $rows = $query->get();
        $advertiserIds = $rows->pluck('advertiser_id')->unique();
        $advertisers = User::whereIn('id', $advertiserIds)->with('userProfile')->get()->keyBy('id');
        $collection = collect($rows->map(fn($r) => (object) $r->toArray()));

        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($advertisers, $search) {
                $advertiser = $advertisers->get($item->advertiser_id);
                if (!$advertiser) return false;
                $name  = trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''));
                return stripos($name, $search) !== false || stripos($advertiser->email ?? '', $search) !== false;
            });
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $collection = $collection->filter(fn($item) => $item->advertiser_id == $advertiserId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($item) => $item->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($item) => $item->month <= $endMonth);
        }

        $collection = $collection->sortByDesc('month')->values();

        foreach ($collection as $row) {
            $advertiser = $advertisers->get($row->advertiser_id);
            $row->advertiser_name  = $advertiser
                ? trim(($advertiser->userProfile->first_name ?? '') . ' ' . ($advertiser->userProfile->last_name ?? ''))
                : 'Unknown';
            $row->advertiser_email = $advertiser->email ?? 'N/A';
            $row->month_formatted  = Carbon::parse($row->month . '-01')->format('F Y');
        }

        $filename = 'advertiser_reports_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($collection) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Period', 'Advertiser Name', 'Advertiser Email', 'Impressions', 'Clicks', 'Conversions', 'Spend (€)', 'CTR (%)', 'ECPM (€)']);
            foreach ($collection as $row) {
                fputcsv($file, [
                    $row->month_formatted,
                    $row->advertiser_name,
                    $row->advertiser_email,
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
