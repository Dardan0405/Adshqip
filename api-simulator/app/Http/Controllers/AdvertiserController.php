<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Notification;
use App\Models\StatDaily;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvertiserController extends Controller
{
    public function dashboard()
    {
        $user   = Auth::user();
        $userId = $user->id;
        $now    = now();

        $today = $now->toDateString();
        $d7    = $now->copy()->subDays(7)->toDateString();
        $d30   = $now->copy()->subDays(30)->toDateString();
        $d31   = $now->copy()->subDays(31)->toDateString();
        $d60   = $now->copy()->subDays(60)->toDateString();

        // ── Stats ────────────────────────────────────────────────────────────
        $totalCampaigns  = Campaign::where('advertiser_id', $userId)->where('is_deleted', false)->count();
        $activeCampaigns = Campaign::where('advertiser_id', $userId)->whereIn('status', ['active', 'running'])->where('is_deleted', false)->count();
        $balance         = (float) ($user->profile?->balance ?? 0);

        $spendings = (float) Transaction::where('user_id', $userId)
            ->where('type', 'ad_spend')
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $d7)
            ->sum('amount');

        $revenueLastWeek = (float) StatDaily::where('advertiser_id', $userId)->where('date', '>=', $d7)->sum('revenue');
        $roi = $spendings > 0 ? round(($revenueLastWeek - $spendings) / $spendings * 100, 1) : 0;

        $stats = [
            'total_campaigns'  => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'balance'          => $balance,
            'spendings'        => $spendings,
            'roi'              => $roi,
        ];

        // ── Metrics (last 30 days vs previous 30 days) ───────────────────────
        $pct = fn ($curr, $prev) => $prev > 0 ? round(($curr - $prev) / $prev * 100, 1) : ($curr > 0 ? 100.0 : 0.0);

        $currRow = StatDaily::where('advertiser_id', $userId)
            ->whereBetween('date', [$d30, $today])
            ->selectRaw('COALESCE(SUM(impressions),0) as imp, COALESCE(SUM(clicks),0) as clk, COALESCE(SUM(conversions),0) as conv')
            ->first();

        $prevRow = StatDaily::where('advertiser_id', $userId)
            ->whereBetween('date', [$d60, $d31])
            ->selectRaw('COALESCE(SUM(impressions),0) as imp, COALESCE(SUM(clicks),0) as clk, COALESCE(SUM(conversions),0) as conv')
            ->first();

        $imp  = (int) ($currRow?->imp  ?? 0);
        $clk  = (int) ($currRow?->clk  ?? 0);
        $conv = (int) ($currRow?->conv ?? 0);
        $pImp = (int) ($prevRow?->imp  ?? 0);
        $pClk = (int) ($prevRow?->clk  ?? 0);
        $pConv = (int) ($prevRow?->conv ?? 0);

        $ctr  = $imp  > 0 ? round($clk  / $imp  * 100, 2) : 0.0;
        $pCtr = $pImp > 0 ? round($pClk / $pImp * 100, 2) : 0.0;
        $apc  = $clk  > 0 ? round($conv  / $clk  * 100, 1) : 0.0;
        $pApc = $pClk > 0 ? round($pConv / $pClk * 100, 1) : 0.0;

        $metrics = [
            'impressions'        => $imp,
            'impressions_change' => $pct($imp, $pImp),
            'clicks'             => $clk,
            'clicks_change'      => $pct($clk, $pClk),
            'conversions'        => $conv,
            'conversions_change' => $pct($conv, $pConv),
            'ctr'                => $ctr,
            'ctr_change'         => $pct($ctr, $pCtr),
            'activity_per_click' => $apc,
            'apc_change'         => $pct($apc, $pApc),
        ];

        // ── Chart (last 14 days) ─────────────────────────────────────────────
        $chartRows = StatDaily::where('advertiser_id', $userId)
            ->where('date', '>=', $now->copy()->subDays(13)->toDateString())
            ->selectRaw('date, SUM(impressions) as imp, SUM(clicks) as clk')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => $r->date->toDateString());

        $maxImp = max(1, $chartRows->max('imp'));

        $chartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $dateStr = $now->copy()->subDays($i)->toDateString();
            $r   = $chartRows->get($dateStr);
            $di  = (int) ($r?->imp ?? 0);
            $dc  = (int) ($r?->clk ?? 0);
            $impPct = (int) round($di / $maxImp * 90);
            $clkPct = (int) min($impPct, round($dc / $maxImp * 90));
            $chartData[] = [
                'label'           => date('M d', strtotime($dateStr)),
                'impressions_pct' => $impPct,
                'clicks_pct'      => $clkPct,
            ];
        }

        // ── Campaigns with aggregate stats ───────────────────────────────────
        $cStats = StatDaily::where('advertiser_id', $userId)
            ->selectRaw('campaign_id, SUM(impressions) as imp, SUM(clicks) as clk, SUM(conversions) as conv, SUM(revenue) as rev')
            ->groupBy('campaign_id')
            ->get()
            ->keyBy('campaign_id');

        $campaigns = Campaign::where('advertiser_id', $userId)
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Campaign $c) use ($cStats) {
                $s   = $cStats->get($c->id);
                $ci  = (int) ($s?->imp  ?? 0);
                $cc  = (int) ($s?->clk  ?? 0);
                $cv  = (int) ($s?->conv ?? 0);
                $rev = (float) ($s?->rev ?? 0);
                $ctr = $ci > 0 ? round($cc / $ci * 100, 2) : 0;
                $cvr = $cc > 0 ? round($cv / $cc * 100, 2) : 0;

                $budget = $c->daily_budget
                    ? 'EUR ' . number_format((float) $c->daily_budget, 2) . '/day'
                    : ($c->total_budget ? 'EUR ' . number_format((float) $c->total_budget, 2) . ' total' : '—');

                return [
                    'name'            => $c->name,
                    'status'          => $c->status,
                    'clicks'          => $cc,
                    'ctr'             => $ctr,
                    'impressions'     => $ci,
                    'conversions'     => $cv,
                    'conversion_rate' => $cvr,
                    'start_date'      => $c->start_date?->format('M d, Y') ?? '—',
                    'end_date'        => $c->end_date?->format('M d, Y') ?? '—',
                    'spent'           => 'EUR ' . number_format($rev, 2),
                    'budget_type'     => $budget,
                ];
            })
            ->all();

        return view('advertiser.dashboard', compact('stats', 'metrics', 'chartData', 'campaigns'));
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
}
