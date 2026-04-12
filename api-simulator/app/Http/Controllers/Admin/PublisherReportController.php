<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublisherReportController extends Controller
{
    public function index(Request $request)
    {
        $query = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m')  as month,
                aq_stats_daily.publisher_id,
                SUM(aq_stats_daily.impressions)            as total_impressions,
                SUM(aq_stats_daily.unique_impressions)     as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                 as total_clicks,
                SUM(aq_stats_daily.unique_clicks)          as total_unique_clicks,
                SUM(aq_stats_daily.conversions)            as total_conversions,
                SUM(aq_stats_daily.publisher_earnings)     as total_earnings,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.clicks) / SUM(aq_stats_daily.impressions)) * 100, 2)
                    ELSE 0
                END as ctr,
                CASE
                    WHEN SUM(aq_stats_daily.impressions) > 0
                    THEN ROUND((SUM(aq_stats_daily.publisher_earnings) / SUM(aq_stats_daily.impressions)) * 1000, 2)
                    ELSE 0
                END as ecpm
            ")
            ->join('aq_users', 'aq_stats_daily.publisher_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'publisher')
            ->whereNotNull('aq_stats_daily.publisher_id')
            ->groupBy('month', 'aq_stats_daily.publisher_id');

        $rows = $query->get();

        $publisherIds = $rows->pluck('publisher_id')->unique();
        $publishers   = User::whereIn('id', $publisherIds)->with('userProfile')->get()->keyBy('id');
        $collection   = collect($rows->map(fn($r) => (object) $r->toArray()));

        // ── Filters ───────────────────────────────────────────────────────
        if ($search = $request->get('search')) {
            $collection = $collection->filter(function ($item) use ($publishers, $search) {
                $pub   = $publishers->get($item->publisher_id);
                $name  = $pub ? trim(($pub->userProfile->first_name ?? '') . ' ' . ($pub->userProfile->last_name ?? '')) : '';
                return stripos($name, $search) !== false || stripos($pub->email ?? '', $search) !== false;
            });
        }

        if ($publisherId = $request->get('publisher_id')) {
            $collection = $collection->filter(fn($i) => $i->publisher_id == $publisherId);
        }

        if ($startMonth = $request->get('start_month')) {
            $collection = $collection->filter(fn($i) => $i->month >= $startMonth);
        }

        if ($endMonth = $request->get('end_month')) {
            $collection = $collection->filter(fn($i) => $i->month <= $endMonth);
        }

        $collection = $collection->sortByDesc('month')->values();

        $totalClicks            = $collection->sum('total_clicks');
        $totalUniqueImpressions = $collection->sum('total_unique_impressions');
        $totalUniqueClicks      = $collection->sum('total_unique_clicks');
        $totalConversions       = $collection->sum('total_conversions');
        $totalImpressions       = $collection->sum('total_impressions');
        $totalEarnings          = $collection->sum('total_earnings');
        $activePublishers       = $collection->pluck('publisher_id')->unique()->count();

        $perPage     = 20;
        $currentPage = $request->get('page', 1);
        $reports     = new \Illuminate\Pagination\LengthAwarePaginator(
            $collection->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $collection->count(), $perPage, $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        foreach ($reports as $row) {
            $pub                  = $publishers->get($row->publisher_id);
            $row->publisher_name  = $pub ? trim(($pub->userProfile->first_name ?? '') . ' ' . ($pub->userProfile->last_name ?? '')) : 'Unknown';
            $row->publisher_email = $pub->email ?? 'N/A';
            $row->month_formatted = Carbon::parse($row->month . '-01')->format('F Y');
        }

        $allPublishers = User::where('role', 'publisher')->with('userProfile')->orderBy('email')->get();

        return view('admin.publisher-reports.index', compact(
            'reports', 'totalClicks', 'totalUniqueImpressions', 'totalUniqueClicks',
            'totalConversions', 'totalImpressions', 'totalEarnings', 'activePublishers', 'allPublishers'
        ));
    }

    public function export(Request $request)
    {
        $rows = StatDaily::selectRaw("
                DATE_FORMAT(aq_stats_daily.date, '%Y-%m') as month,
                aq_stats_daily.publisher_id,
                SUM(aq_stats_daily.impressions)           as total_impressions,
                SUM(aq_stats_daily.unique_impressions)    as total_unique_impressions,
                SUM(aq_stats_daily.clicks)                as total_clicks,
                SUM(aq_stats_daily.unique_clicks)         as total_unique_clicks,
                SUM(aq_stats_daily.conversions)           as total_conversions,
                SUM(aq_stats_daily.publisher_earnings)    as total_earnings,
                CASE WHEN SUM(aq_stats_daily.impressions) > 0
                     THEN ROUND((SUM(aq_stats_daily.clicks)/SUM(aq_stats_daily.impressions))*100,2)
                     ELSE 0 END as ctr,
                CASE WHEN SUM(aq_stats_daily.impressions) > 0
                     THEN ROUND((SUM(aq_stats_daily.publisher_earnings)/SUM(aq_stats_daily.impressions))*1000,2)
                     ELSE 0 END as ecpm
            ")
            ->join('aq_users', 'aq_stats_daily.publisher_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'publisher')
            ->whereNotNull('aq_stats_daily.publisher_id')
            ->groupBy('month', 'aq_stats_daily.publisher_id')
            ->get();

        $publishers = User::whereIn('id', $rows->pluck('publisher_id')->unique())->with('userProfile')->get()->keyBy('id');
        $collection = collect($rows->map(fn($r) => (object) $r->toArray()));

        if ($s = $request->get('search')) {
            $collection = $collection->filter(fn($i) => stripos(($publishers->get($i->publisher_id)?->email ?? ''), $s) !== false);
        }
        if ($id = $request->get('publisher_id')) { $collection = $collection->filter(fn($i) => $i->publisher_id == $id); }
        if ($s = $request->get('start_month'))   { $collection = $collection->filter(fn($i) => $i->month >= $s); }
        if ($e = $request->get('end_month'))     { $collection = $collection->filter(fn($i) => $i->month <= $e); }
        $collection = $collection->sortByDesc('month')->values();

        foreach ($collection as $row) {
            $pub = $publishers->get($row->publisher_id);
            $row->publisher_name  = $pub ? trim(($pub->userProfile->first_name ?? '') . ' ' . ($pub->userProfile->last_name ?? '')) : 'Unknown';
            $row->publisher_email = $pub->email ?? 'N/A';
            $row->month_formatted = Carbon::parse($row->month . '-01')->format('F Y');
        }

        return response()->stream(function () use ($collection) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Period', 'Publisher Name', 'Publisher Email', 'Impressions', 'Clicks', 'Conversions', 'Earnings (€)', 'CTR (%)', 'ECPM (€)']);
            foreach ($collection as $r) {
                fputcsv($f, [$r->month_formatted, $r->publisher_name, $r->publisher_email,
                    number_format($r->total_impressions, 0, '.', ''), number_format($r->total_clicks, 0, '.', ''),
                    number_format($r->total_conversions, 0, '.', ''), number_format($r->total_earnings, 2, '.', ''),
                    number_format($r->ctr, 2, '.', ''), number_format($r->ecpm, 2, '.', '')]);
            }
            fclose($f);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="publisher_reports_' . date('Y-m-d_His') . '.csv"']);
    }
}
