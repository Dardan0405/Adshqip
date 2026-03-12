<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublisherController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Earnings cards (top row)
        $earnings = [
            'today' => 191.88,
            'this_week' => 1443.93,
            'this_month' => 2283.64,
            'last_month' => 5328.73,
            'forecast' => 5983.14,
            'forecast_pct' => 12,
        ];

        // Impressions / Clicks / Revenue
        $metrics = [
            'impressions' => 714532,
            'impressions_change' => 5.78,
            'impressions_diff' => 39147,
            'clicks' => 625635,
            'clicks_change' => -13,
            'clicks_diff' => 96170,
            'revenue' => 167433.82,
            'revenue_change' => -1.96,
            'revenue_diff' => 3346.21,
        ];

        // Chart data — 16 days
        $days = ['D1','D3','D5','D7','D9','D11','D13','D15','D17','D19','D21','D23','D25','D27','D29','D30'];
        $chartData = [];
        foreach ($days as $day) {
            $impPct = rand(40, 98);
            $profitPct = rand(8, min(45, $impPct - 10));
            $chartData[] = [
                'label' => $day,
                'impressions_pct' => $impPct,
                'profit_pct' => $profitPct,
            ];
        }

        // Revenue - Ad Zones table
        $adZones = [
            ['name' => 'example zone 1', 'revenue' => 6014.03, 'change' => 5.77],
            ['name' => 'example zone 2', 'revenue' => 6014.03, 'change' => -4.05],
            ['name' => 'example zone 3', 'revenue' => 6014.03, 'change' => 1.91],
            ['name' => 'example zone 4', 'revenue' => 6014.03, 'change' => -8.75],
            ['name' => 'example zone 5', 'revenue' => 5424.03, 'change' => 3.28],
        ];

        // CPC - Device Type table
        $deviceCpc = [
            ['type' => 'Desktop', 'cpc' => 0.53, 'change' => 1.78],
            ['type' => 'Mobile', 'cpc' => 0.31, 'change' => -1.62],
            ['type' => 'Tablet', 'cpc' => 0.15, 'change' => 2.94],
            ['type' => 'Smart TV', 'cpc' => 0.15, 'change' => 0],
            ['type' => 'Console', 'cpc' => 0.03, 'change' => 0],
        ];

        $balance = 17491.53;

        return view('publisher.dashboard', compact(
            'earnings', 'metrics', 'chartData', 'adZones', 'deviceCpc', 'balance'
        ));
    }
}
