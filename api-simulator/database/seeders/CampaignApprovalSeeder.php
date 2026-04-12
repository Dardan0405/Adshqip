<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CampaignApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $advertiser = User::updateOrCreate(
            ['email' => 'campaign.approvals@adshqip.com'],
            [
                'password_hash' => Hash::make('password123'),
                'role' => 'advertiser',
                'status' => 'active',
                'preferred_language' => 'en',
                'theme_preference' => 'system',
                'timezone' => 'Europe/Tirane',
                'is_deleted' => false,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $advertiser->id],
            [
                'first_name' => 'Campaign',
                'last_name' => 'Advertiser',
                'company_name' => 'Campaign Approval Demo',
                'country_code' => 'AL',
                'website_url' => 'https://adshqip.com',
                'currency' => 'EUR',
                'balance' => 0,
            ]
        );

        $campaigns = [
            [
                'name' => 'Campaign Approval Demo 001',
                'campaign_type' => 'cpm',
                'marketing_objective' => 'brand_awareness',
                'status' => 'pending_review',
                'bid_amount' => 1.8000,
                'daily_budget' => 120.0000,
                'total_budget' => 2400.0000,
                'remaining_budget' => 2400.0000,
                'admin_approved' => false,
            ],
            [
                'name' => 'Campaign Approval Demo 002',
                'campaign_type' => 'cpc',
                'marketing_objective' => 'traffic',
                'status' => 'pending_review',
                'bid_amount' => 0.6500,
                'daily_budget' => 95.0000,
                'total_budget' => 1600.0000,
                'remaining_budget' => 1600.0000,
                'admin_approved' => false,
            ],
            [
                'name' => 'Campaign Approval Demo 003',
                'campaign_type' => 'cpa',
                'marketing_objective' => 'conversions',
                'status' => 'active',
                'bid_amount' => 3.5000,
                'daily_budget' => 180.0000,
                'total_budget' => 3200.0000,
                'remaining_budget' => 2800.0000,
                'admin_approved' => true,
            ],
            [
                'name' => 'Campaign Approval Demo 004',
                'campaign_type' => 'cpv',
                'marketing_objective' => 'video_views',
                'status' => 'rejected',
                'bid_amount' => 0.1200,
                'daily_budget' => 75.0000,
                'total_budget' => 1400.0000,
                'remaining_budget' => 1400.0000,
                'admin_approved' => false,
            ],
        ];

        foreach ($campaigns as $index => $campaign) {
            Campaign::updateOrCreate(
                [
                    'advertiser_id' => $advertiser->id,
                    'name' => $campaign['name'],
                ],
                [
                    'description' => 'Seeded campaign approval record for the admin approvals page.',
                    'format_id' => null,
                    'marketing_objective' => $campaign['marketing_objective'],
                    'campaign_type' => $campaign['campaign_type'],
                    'status' => $campaign['status'],
                    'bid_amount' => $campaign['bid_amount'],
                    'daily_budget' => $campaign['daily_budget'],
                    'total_budget' => $campaign['total_budget'],
                    'remaining_budget' => $campaign['remaining_budget'],
                    'currency' => 'EUR',
                    'start_date' => now()->subDays(7 + $index),
                    'end_date' => now()->addDays(30 + $index),
                    'frequency_cap' => 3,
                    'frequency_cap_period' => 'day',
                    'targeting_geo' => ['countries' => ['AL', 'XK']],
                    'targeting_device' => ['desktop', 'mobile'],
                    'weight' => 5,
                    'admin_approved' => $campaign['admin_approved'],
                    'is_deleted' => false,
                ]
            );
        }
    }
}
