<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DirectCampaignApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $advertiser = User::updateOrCreate(
            ['email' => 'direct.approvals@adshqip.com'],
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
                'first_name' => 'Direct',
                'last_name' => 'Advertiser',
                'company_name' => 'Adshqip Direct Deals',
                'phone' => '+355690000000',
                'country_code' => 'AL',
                'website_url' => 'https://adshqip.com',
                'currency' => 'EUR',
                'balance' => 0,
            ]
        );

        $campaigns = [
            [
                'name' => 'Seeded Direct Campaign Approval 001',
                'brand_name' => 'Balkan Travel Plus',
                'pricing_model' => 'cpm',
                'marketing_objective' => 'brand_awareness',
                'bid_amount' => 2.7500,
                'daily_budget' => 120.0000,
                'total_budget' => 1800.0000,
                'remaining_budget' => 1800.0000,
                'status' => 'pending_review',
                'admin_approved' => false,
                'rejection_reason' => null,
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Seeded Direct Campaign Approval 002',
                'brand_name' => 'Kosova Mobile Week',
                'pricing_model' => 'cpc',
                'marketing_objective' => 'traffic',
                'bid_amount' => 0.8500,
                'daily_budget' => 80.0000,
                'total_budget' => 950.0000,
                'remaining_budget' => 950.0000,
                'status' => 'pending_review',
                'admin_approved' => false,
                'rejection_reason' => null,
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'Seeded Direct Campaign Approval 003',
                'brand_name' => 'Albania Commerce Boost',
                'pricing_model' => 'flat_rate',
                'marketing_objective' => 'conversions',
                'bid_amount' => 150.0000,
                'daily_budget' => 150.0000,
                'total_budget' => 2400.0000,
                'remaining_budget' => 1200.0000,
                'status' => 'active',
                'admin_approved' => true,
                'rejection_reason' => null,
                'created_at' => now()->subDays(12),
            ],
            [
                'name' => 'Seeded Direct Campaign Approval 004',
                'brand_name' => 'Adriatic Video Launch',
                'pricing_model' => 'cpv',
                'marketing_objective' => 'video_views',
                'bid_amount' => 0.1200,
                'daily_budget' => 65.0000,
                'total_budget' => 700.0000,
                'remaining_budget' => 700.0000,
                'status' => 'rejected',
                'admin_approved' => false,
                'rejection_reason' => 'Creative requires revision before activation.',
                'created_at' => now()->subDays(9),
            ],
        ];

        foreach ($campaigns as $campaign) {
            DB::table('aq_direct_campaigns')->updateOrInsert(
                [
                    'advertiser_id' => $advertiser->id,
                    'name' => $campaign['name'],
                ],
                [
                    'description' => 'Seeded direct campaign approval record for the admin approvals page.',
                    'format_id' => null,
                    'marketing_objective' => $campaign['marketing_objective'],
                    'pricing_model' => $campaign['pricing_model'],
                    'bid_amount' => $campaign['bid_amount'],
                    'daily_budget' => $campaign['daily_budget'],
                    'total_budget' => $campaign['total_budget'],
                    'remaining_budget' => $campaign['remaining_budget'],
                    'currency' => 'EUR',
                    'schedule_timezone' => 'Europe/Tirane',
                    'delivery_mode' => 'standard',
                    'priority' => 5,
                    'weight' => 1,
                    'destination_url' => 'https://adshqip.com/direct-campaign-demo',
                    'display_url' => 'adshqip.com/direct-campaign-demo',
                    'headline' => $campaign['brand_name'],
                    'body_text' => 'Sample direct campaign seeded for approval workflow testing.',
                    'call_to_action' => 'Learn More',
                    'sponsored_label' => 'Sponsored',
                    'brand_name' => $campaign['brand_name'],
                    'status' => $campaign['status'],
                    'admin_approved' => $campaign['admin_approved'],
                    'rejection_reason' => $campaign['rejection_reason'],
                    'is_deleted' => false,
                    'created_at' => $campaign['created_at'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
