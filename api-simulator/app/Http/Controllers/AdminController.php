<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Top stat cards
        $stats = [
            'total_revenue' => 347891.42,
            'revenue_change' => 12.4,
            'active_users' => 2847,
            'users_change' => 8.2,
            'impressions' => 48723150,
            'impressions_change' => 5.1,
            'fraud_blocked' => 14382,
            'fraud_change' => 3.7,
            'total_users' => 3214,
            'new_users_month' => 187,
            'total_campaigns' => 1456,
            'pending_review' => 23,
        ];

        // Revenue chart — 30 days
        $chartData = [];
        for ($i = 1; $i <= 30; $i++) {
            $chartData[] = [
                'label' => sprintf('%02d', $i),
                'revenue_pct' => rand(30, 95),
                'payout_pct' => rand(10, 35),
            ];
        }

        // User breakdown
        $userBreakdown = [
            ['role' => 'advertiser', 'count' => 1842, 'pct' => 57, 'color' => 'bg-blue-500'],
            ['role' => 'publisher', 'count' => 1024, 'pct' => 32, 'color' => 'bg-green-500'],
            ['role' => 'admin', 'count' => 12, 'pct' => 0.4, 'color' => 'bg-indigo-500'],
            ['role' => 'manager', 'count' => 36, 'pct' => 1.1, 'color' => 'bg-orange-500'],
        ];

        // Recent users
        $recentUsers = [
            ['email' => 'newadvertiser@test.com', 'role' => 'advertiser', 'joined' => '2 hours ago', 'avatar_bg' => 'bg-blue-500', 'role_color' => 'bg-blue-100 text-blue-700'],
            ['email' => 'publisher42@media.al', 'role' => 'publisher', 'joined' => '5 hours ago', 'avatar_bg' => 'bg-green-500', 'role_color' => 'bg-green-100 text-green-700'],
            ['email' => 'balkanads@gmail.com', 'role' => 'advertiser', 'joined' => '1 day ago', 'avatar_bg' => 'bg-purple-500', 'role_color' => 'bg-blue-100 text-blue-700'],
            ['email' => 'news-portal@kosovo.net', 'role' => 'publisher', 'joined' => '2 days ago', 'avatar_bg' => 'bg-teal-500', 'role_color' => 'bg-green-100 text-green-700'],
            ['email' => 'ecom-shop@adshqip.com', 'role' => 'advertiser', 'joined' => '3 days ago', 'avatar_bg' => 'bg-pink-500', 'role_color' => 'bg-blue-100 text-blue-700'],
        ];

        // Open support tickets
        $openTickets = [
            ['subject' => 'Payment not received', 'priority' => 'urgent', 'category' => 'billing', 'created' => '1 hour ago'],
            ['subject' => 'Campaign rejected without reason', 'priority' => 'high', 'category' => 'campaign', 'created' => '3 hours ago'],
            ['subject' => 'Ad code not loading', 'priority' => 'medium', 'category' => 'technical', 'created' => '6 hours ago'],
            ['subject' => 'Need account verification', 'priority' => 'low', 'category' => 'account', 'created' => '1 day ago'],
            ['subject' => 'Suspicious click activity', 'priority' => 'high', 'category' => 'fraud', 'created' => '1 day ago'],
        ];

        // Campaign status summary
        $campaignStatus = [
            ['label' => 'active', 'count' => 834, 'dot' => 'bg-green-500'],
            ['label' => 'paused', 'count' => 215, 'dot' => 'bg-yellow-500'],
            ['label' => 'draft', 'count' => 189, 'dot' => 'bg-gray-400'],
            ['label' => 'completed', 'count' => 142, 'dot' => 'bg-blue-500'],
            ['label' => 'rejected', 'count' => 53, 'dot' => 'bg-red-500'],
            ['label' => 'pending review', 'count' => 23, 'dot' => 'bg-orange-500'],
        ];

        return view('admin.dashboard', compact(
            'stats', 'chartData', 'userBreakdown', 'recentUsers', 'openTickets', 'campaignStatus'
        ));
    }
}
