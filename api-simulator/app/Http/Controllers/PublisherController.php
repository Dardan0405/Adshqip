<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Payout;
use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublisherController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();
        $today  = now()->toDateString();

        // ── Period boundaries ────────────────────────────────────────────────
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthEnd   = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $thisWeekStart  = now()->startOfWeek()->toDateString();
        $cur30Start     = now()->subDays(29)->toDateString();
        $prev30Start    = now()->subDays(59)->toDateString();
        $prev30End      = now()->subDays(30)->toDateString();

        // ── Earnings cards ───────────────────────────────────────────────────
        $earnToday     = (float) StatDaily::where('publisher_id', $userId)->where('date', $today)->sum('publisher_earnings');
        $earnWeek      = (float) StatDaily::where('publisher_id', $userId)->whereBetween('date', [$thisWeekStart, $today])->sum('publisher_earnings');
        $earnThisMonth = (float) StatDaily::where('publisher_id', $userId)->whereBetween('date', [$thisMonthStart, $today])->sum('publisher_earnings');
        $earnLastMonth = (float) StatDaily::where('publisher_id', $userId)->whereBetween('date', [$lastMonthStart, $lastMonthEnd])->sum('publisher_earnings');

        $daysElapsed = max(1, now()->day);
        $forecast    = round(($earnThisMonth / $daysElapsed) * now()->daysInMonth, 2);
        $forecastPct = $earnLastMonth > 0 ? round((($forecast - $earnLastMonth) / $earnLastMonth) * 100, 1) : 0;

        $earnings = [
            'today'        => $earnToday,
            'this_week'    => $earnWeek,
            'this_month'   => $earnThisMonth,
            'last_month'   => $earnLastMonth,
            'forecast'     => $forecast,
            'forecast_pct' => $forecastPct,
        ];

        // ── Metrics: last 30 days vs previous 30 days ────────────────────────
        $cur  = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$cur30Start, $today])
            ->selectRaw('SUM(impressions) as imp, SUM(clicks) as clk, SUM(publisher_earnings) as earn')
            ->first();

        $prev = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$prev30Start, $prev30End])
            ->selectRaw('SUM(impressions) as imp, SUM(clicks) as clk, SUM(publisher_earnings) as earn')
            ->first();

        $curImp   = (int)   ($cur->imp   ?? 0);
        $prevImp  = (int)   ($prev->imp  ?? 0);
        $curClk   = (int)   ($cur->clk   ?? 0);
        $prevClk  = (int)   ($prev->clk  ?? 0);
        $curEarn  = (float) ($cur->earn  ?? 0);
        $prevEarn = (float) ($prev->earn ?? 0);

        $metrics = [
            'impressions'       => $curImp,
            'impressions_change' => $prevImp  > 0 ? round((($curImp  - $prevImp)  / $prevImp)  * 100, 2) : 0,
            'impressions_diff'  => abs($curImp  - $prevImp),
            'clicks'            => $curClk,
            'clicks_change'     => $prevClk  > 0 ? round((($curClk  - $prevClk)  / $prevClk)  * 100, 2) : 0,
            'clicks_diff'       => abs($curClk  - $prevClk),
            'revenue'           => $curEarn,
            'revenue_change'    => $prevEarn > 0 ? round((($curEarn - $prevEarn) / $prevEarn) * 100, 2) : 0,
            'revenue_diff'      => abs($curEarn - $prevEarn),
        ];

        // ── Chart data: last 30 days by date ─────────────────────────────────
        $chartRows = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$cur30Start, $today])
            ->selectRaw('date, SUM(impressions) as imp, SUM(publisher_earnings) as earn')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn($r) => \Carbon\Carbon::parse($r->date)->toDateString());

        $maxImpDay  = max(1, $chartRows->max('imp'));
        $maxEarnDay = max(1, $chartRows->max('earn'));

        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $d   = now()->subDays($i)->toDateString();
            $row = $chartRows[$d] ?? null;
            $chartData[] = [
                'label'           => now()->subDays($i)->format('d'),
                'impressions_pct' => $row ? min(88, (int) round(((int) $row->imp  / $maxImpDay)  * 88)) : 0,
                'profit_pct'      => $row ? min(40, (int) round(((float) $row->earn / $maxEarnDay) * 40)) : 0,
            ];
        }

        // ── Revenue by Ad Zone (top 5, last 30 days) ─────────────────────────
        $zoneRevCur = StatDaily::where('aq_stats_daily.publisher_id', $userId)
            ->whereBetween('aq_stats_daily.date', [$cur30Start, $today])
            ->whereNotNull('aq_stats_daily.zone_id')
            ->join('aq_zones', 'aq_stats_daily.zone_id', '=', 'aq_zones.id')
            ->selectRaw('aq_stats_daily.zone_id, aq_zones.name, SUM(aq_stats_daily.publisher_earnings) as revenue')
            ->groupBy('aq_stats_daily.zone_id', 'aq_zones.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $zoneRevPrev = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$prev30Start, $prev30End])
            ->whereNotNull('zone_id')
            ->selectRaw('zone_id, SUM(publisher_earnings) as revenue')
            ->groupBy('zone_id')
            ->get()
            ->keyBy('zone_id');

        $adZones = $zoneRevCur->map(function ($z) use ($zoneRevPrev) {
            $cur    = (float) $z->revenue;
            $prev   = (float) ($zoneRevPrev[$z->zone_id]->revenue ?? 0);
            $change = $prev > 0 ? round((($cur - $prev) / $prev) * 100, 2) : 0;
            return ['name' => $z->name ?: 'Zone #' . $z->zone_id, 'revenue' => $cur, 'change' => $change];
        })->toArray();

        // ── CPC by Device Type ────────────────────────────────────────────────
        $devCur = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$cur30Start, $today])
            ->whereNotNull('device_type')
            ->selectRaw('device_type, SUM(publisher_earnings) as earnings, SUM(clicks) as clicks')
            ->groupBy('device_type')
            ->get()
            ->keyBy('device_type');

        $devPrev = StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [$prev30Start, $prev30End])
            ->whereNotNull('device_type')
            ->selectRaw('device_type, SUM(publisher_earnings) as earnings, SUM(clicks) as clicks')
            ->groupBy('device_type')
            ->get()
            ->keyBy('device_type');

        $deviceCpc = [];
        foreach (['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'] as $key => $label) {
            $c      = $devCur[$key]  ?? null;
            $p      = $devPrev[$key] ?? null;
            $curCpc  = ($c && (int) $c->clicks  > 0) ? round((float) $c->earnings  / (int) $c->clicks,  4) : 0;
            $prevCpc = ($p && (int) $p->clicks  > 0) ? round((float) $p->earnings  / (int) $p->clicks,  4) : 0;
            $change  = $prevCpc > 0 ? round((($curCpc - $prevCpc) / $prevCpc) * 100, 2) : 0;
            $deviceCpc[] = ['type' => $label, 'cpc' => $curCpc, 'change' => $change];
        }

        // ── Balance (header widget) ───────────────────────────────────────────
        $totalEarned  = (float) StatDaily::where('publisher_id', $userId)->sum('publisher_earnings');
        $totalPaidOut = (float) Payout::where('user_id', $userId)->where('status', 'completed')->sum('amount');
        $balance      = max(0.0, $totalEarned - $totalPaidOut);

        return view('publisher.dashboard', compact(
            'earnings', 'metrics', 'chartData', 'adZones', 'deviceCpc', 'balance'
        ));
    }

    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return response()->json($notifications);
    }

    public function markNotificationRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function messages()
    {
        $messages = Notification::where('user_id', Auth::id())
            ->whereIn('type', ['info', 'system'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json($messages);
    }
}
