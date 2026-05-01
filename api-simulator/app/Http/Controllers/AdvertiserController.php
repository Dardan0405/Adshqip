<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\DirectCampaign;
use App\Models\DirectCampaignStat;
use App\Models\Notification;
use App\Models\PlatformAnnouncement;
use App\Models\StatDaily;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvertiserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user()->loadMissing('profile');
        $userId = $user->id;
        $now = now();

        $today = $now->toDateString();
        $d7 = $now->copy()->subDays(6)->toDateString();
        $d14 = $now->copy()->subDays(13)->toDateString();
        $d30 = $now->copy()->subDays(29)->toDateString();
        $d31 = $now->copy()->subDays(30)->toDateString();
        $d60 = $now->copy()->subDays(59)->toDateString();

        $currencyCode = $user->profile?->currency ?: 'EUR';
        $formatMoney = fn (float $amount, ?string $currency = null) => $this->formatMoney($amount, $currency ?: $currencyCode);

        $regularCampaignCount = Campaign::where('advertiser_id', $userId)->where('is_deleted', false)->count();
        $directCampaignCount = DirectCampaign::where('advertiser_id', $userId)->where('is_deleted', false)->count();
        $totalCampaigns = $regularCampaignCount + $directCampaignCount;

        $activeCampaigns = Campaign::where('advertiser_id', $userId)
            ->whereIn('status', ['active', 'running'])
            ->where('is_deleted', false)
            ->count()
            + DirectCampaign::where('advertiser_id', $userId)
                ->whereIn('status', ['active', 'running', 'approved'])
                ->where('is_deleted', false)
                ->count();

        $regularSeven = $this->regularStats($userId, $d7, $today);
        $directSeven = $this->directStats($userId, $d7, $today);
        $spendings = (float) $regularSeven->rev + (float) $directSeven->rev;

        if ($spendings <= 0.0) {
            $spendings = (float) Transaction::where('user_id', $userId)
                ->where('type', 'ad_spend')
                ->where('status', 'completed')
                ->whereDate('created_at', '>=', $d7)
                ->sum('amount');
        }

        $conversionRevenue = (float) DB::table('aq_conversions')
            ->join('aq_campaigns', 'aq_campaigns.id', '=', 'aq_conversions.campaign_id')
            ->where('aq_campaigns.advertiser_id', $userId)
            ->whereDate('aq_conversions.created_at', '>=', $d7)
            ->whereDate('aq_conversions.created_at', '<=', $today)
            ->sum('aq_conversions.revenue');

        $stats = [
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'balance' => (float) ($user->profile?->balance ?? 0),
            'spendings' => $spendings,
            'roi' => $spendings > 0 && $conversionRevenue > 0
                ? round((($conversionRevenue - $spendings) / $spendings) * 100, 1)
                : 0.0,
        ];

        $regularCurr = $this->regularStats($userId, $d30, $today);
        $directCurr = $this->directStats($userId, $d30, $today);
        $regularPrev = $this->regularStats($userId, $d60, $d31);
        $directPrev = $this->directStats($userId, $d60, $d31);

        $imp = (int) $regularCurr->imp + (int) $directCurr->imp;
        $clk = (int) $regularCurr->clk + (int) $directCurr->clk;
        $conv = (int) $regularCurr->conv + (int) $directCurr->conv;
        $pImp = (int) $regularPrev->imp + (int) $directPrev->imp;
        $pClk = (int) $regularPrev->clk + (int) $directPrev->clk;
        $pConv = (int) $regularPrev->conv + (int) $directPrev->conv;

        $ctr = $imp > 0 ? round($clk / $imp * 100, 2) : 0.0;
        $pCtr = $pImp > 0 ? round($pClk / $pImp * 100, 2) : 0.0;
        $apc = $clk > 0 ? round($conv / $clk, 3) : 0.0;
        $pApc = $pClk > 0 ? round($pConv / $pClk, 3) : 0.0;

        $metrics = [
            'impressions' => $imp,
            'impressions_change' => $this->percentageChange($imp, $pImp),
            'clicks' => $clk,
            'clicks_change' => $this->percentageChange($clk, $pClk),
            'conversions' => $conv,
            'conversions_change' => $this->percentageChange($conv, $pConv),
            'ctr' => $ctr,
            'ctr_change' => $this->percentageChange($ctr, $pCtr),
            'activity_per_click' => $apc,
            'apc_change' => $this->percentageChange($apc, $pApc),
        ];

        $chartRows = StatDaily::where('advertiser_id', $userId)
            ->whereBetween('date', [$d14, $today])
            ->selectRaw('date, SUM(impressions) as imp, SUM(clicks) as clk')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($row) => $row->date->toDateString());

        $directChartRows = DirectCampaignStat::query()
            ->whereHas('campaign', fn ($query) => $query->where('advertiser_id', $userId)->where('is_deleted', false))
            ->whereBetween('date', [$d14, $today])
            ->selectRaw('date, SUM(impressions) as imp, SUM(clicks) as clk')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($row) => $row->date->toDateString());

        $maxImp = 1;
        for ($i = 13; $i >= 0; $i--) {
            $dateStr = $now->copy()->subDays($i)->toDateString();
            $maxImp = max(
                $maxImp,
                (int) ($chartRows->get($dateStr)?->imp ?? 0) + (int) ($directChartRows->get($dateStr)?->imp ?? 0)
            );
        }

        $chartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $dateStr = $now->copy()->subDays($i)->toDateString();
            $di = (int) ($chartRows->get($dateStr)?->imp ?? 0) + (int) ($directChartRows->get($dateStr)?->imp ?? 0);
            $dc = (int) ($chartRows->get($dateStr)?->clk ?? 0) + (int) ($directChartRows->get($dateStr)?->clk ?? 0);

            $chartData[] = [
                'label' => date('M d', strtotime($dateStr)),
                'impressions_pct' => $this->chartPercent($di, $maxImp),
                'clicks_pct' => $this->chartPercent($dc, $maxImp),
                'impressions' => $di,
                'clicks' => $dc,
            ];
        }

        $chartAxis = [
            'max' => $maxImp,
            'labels' => [
                $this->compactNumber($maxImp),
                $this->compactNumber((int) round($maxImp * 0.75)),
                $this->compactNumber((int) round($maxImp * 0.5)),
                $this->compactNumber((int) round($maxImp * 0.25)),
                '0',
            ],
        ];

        $campaigns = $this->dashboardCampaigns($userId, $formatMoney);

        $announcement = PlatformAnnouncement::visible()
            ->forAudience('advertiser')
            ->where('placement', 'dashboard')
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->first();

        return view('advertiser.dashboard', compact(
            'stats',
            'metrics',
            'chartData',
            'chartAxis',
            'campaigns',
            'announcement',
            'currencyCode'
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

    private function dashboardCampaigns(int $userId, callable $formatMoney): array
    {
        $regularStats = StatDaily::where('advertiser_id', $userId)
            ->selectRaw('campaign_id, SUM(impressions) as imp, SUM(clicks) as clk, SUM(conversions) as conv, SUM(revenue) as rev')
            ->groupBy('campaign_id')
            ->get()
            ->keyBy('campaign_id');

        $regularCampaigns = Campaign::where('advertiser_id', $userId)
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Campaign $campaign) use ($regularStats, $formatMoney) {
                $row = $regularStats->get($campaign->id);

                return $this->campaignRow(
                    $campaign,
                    'Campaign',
                    (int) ($row?->imp ?? 0),
                    (int) ($row?->clk ?? 0),
                    (int) ($row?->conv ?? 0),
                    (float) ($row?->rev ?? 0),
                    $formatMoney,
                    route('advertiser.campaigns.show', $campaign->id),
                    route('advertiser.campaigns.edit', $campaign->id),
                    route('advertiser.reports.campaign', ['campaign_id' => $campaign->id])
                );
            });

        $directStats = DirectCampaignStat::query()
            ->whereHas('campaign', fn ($query) => $query->where('advertiser_id', $userId)->where('is_deleted', false))
            ->selectRaw('campaign_id, SUM(impressions) as imp, SUM(clicks) as clk, SUM(conversions) as conv, SUM(revenue) as rev')
            ->groupBy('campaign_id')
            ->get()
            ->keyBy('campaign_id');

        $directCampaigns = DirectCampaign::where('advertiser_id', $userId)
            ->where('is_deleted', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (DirectCampaign $campaign) use ($directStats, $formatMoney) {
                $row = $directStats->get($campaign->id);

                return $this->campaignRow(
                    $campaign,
                    'Direct',
                    (int) ($row?->imp ?? 0),
                    (int) ($row?->clk ?? 0),
                    (int) ($row?->conv ?? 0),
                    (float) ($row?->rev ?? 0),
                    $formatMoney,
                    route('advertiser.direct-campaigns.show', $campaign->id),
                    route('advertiser.direct-campaigns.edit', $campaign->id),
                    route('advertiser.reports.campaign', ['direct_campaign_id' => $campaign->id])
                );
            });

        return $regularCampaigns
            ->concat($directCampaigns)
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    private function campaignRow($campaign, string $type, int $impressions, int $clicks, int $conversions, float $spend, callable $formatMoney, string $showUrl, string $editUrl, string $statsUrl): array
    {
        $currency = $campaign->currency ?: 'EUR';
        $budget = $campaign->daily_budget
            ? $formatMoney((float) $campaign->daily_budget, $currency) . '/day'
            : ($campaign->total_budget ? $formatMoney((float) $campaign->total_budget, $currency) . ' total' : '-');

        return [
            'id' => $campaign->id,
            'type' => $type,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0,
            'impressions' => $impressions,
            'conversions' => $conversions,
            'conversion_rate' => $clicks > 0 ? round($conversions / $clicks * 100, 2) : 0,
            'start_date' => $campaign->start_date?->format('M d, Y') ?? '-',
            'end_date' => $campaign->end_date?->format('M d, Y') ?? '-',
            'spent' => $formatMoney($spend, $currency),
            'budget_type' => $budget,
            'created_at' => $campaign->created_at,
            'show_url' => $showUrl,
            'edit_url' => $editUrl,
            'stats_url' => $statsUrl,
        ];
    }

    private function regularStats(int $userId, string $startDate, string $endDate)
    {
        return StatDaily::where('advertiser_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(impressions),0) as imp, COALESCE(SUM(clicks),0) as clk, COALESCE(SUM(conversions),0) as conv, COALESCE(SUM(revenue),0) as rev')
            ->first();
    }

    private function directStats(int $userId, string $startDate, string $endDate)
    {
        return DirectCampaignStat::query()
            ->whereHas('campaign', fn ($query) => $query->where('advertiser_id', $userId)->where('is_deleted', false))
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(impressions),0) as imp, COALESCE(SUM(clicks),0) as clk, COALESCE(SUM(conversions),0) as conv, COALESCE(SUM(revenue),0) as rev')
            ->first();
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100.0 : 0.0);
    }

    private function chartPercent(int $value, int $max): int
    {
        if ($value <= 0 || $max <= 0) {
            return 0;
        }

        return (int) max(4, round(($value / $max) * 90));
    }

    private function compactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1) . 'M';
        }

        if ($value >= 1000) {
            return number_format($value / 1000, 1) . 'K';
        }

        return number_format($value);
    }

    private function formatMoney(float $amount, string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => 'EUR ',
            'ALL' => 'L ',
            'GBP' => 'GBP ',
        ];

        return ($symbols[$currency] ?? $currency . ' ') . number_format($amount, 2);
    }
}
