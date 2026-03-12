<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvertiserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Demo data matching the screenshot design
        $stats = [
            'total_campaigns' => 32,
            'active_campaigns' => 26,
            'balance' => 172.12,
            'spendings' => 19.89,
            'roi' => 15,
        ];

        $metrics = [
            'impressions' => 1324,
            'impressions_change' => 17,
            'clicks' => 532,
            'clicks_change' => -5,
            'conversions' => 213,
            'conversions_change' => 8,
            'ctr' => 0.19,
            'ctr_change' => 7,
            'activity_per_click' => 53.6,
            'apc_change' => 4,
        ];

        // Chart data — 14 days
        $days = ['Aug 01','Aug 03','Aug 05','Aug 07','Aug 09','Aug 11','Aug 13','Aug 15','Aug 17','Aug 19','Aug 21','Aug 23','Aug 25','Aug 27'];
        $chartData = [];
        foreach ($days as $day) {
            $impPct = rand(30, 95);
            $clickPct = rand(5, min(40, $impPct - 5));
            $chartData[] = [
                'label' => substr($day, 4),
                'impressions_pct' => $impPct,
                'clicks_pct' => $clickPct,
            ];
        }

        // Demo campaigns matching the screenshot
        $campaigns = [
            ['name' => 'Test Campaign, August, 2022', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => 'Jul 01, 2025', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Cylon, May Sep, 2022', 'status' => 'disabled', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'New Product Launch Campaign, Jan Dec', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => 'Sep 01, 2022', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'My Campaign', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => 'Jul 01, 2025', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Desk, August', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Website Campaign', 'status' => 'paused', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Campaign 03', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'All year, campaign', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => 'Jul 01, 2025', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Test Campaign1, July 2022', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
            ['name' => 'Test Campaign4, May 2022', 'status' => 'running', 'clicks' => 80, 'ctr' => '5.3', 'impressions' => 1714, 'conversions' => 33, 'conversion_rate' => '5.3', 'start_date' => 'Jul 18, 2022', 'end_date' => '—', 'spent' => 'RM of $300', 'budget_type' => 'filled monthly'],
        ];

        return view('advertiser.dashboard', compact('stats', 'metrics', 'chartData', 'campaigns'));
    }
}
