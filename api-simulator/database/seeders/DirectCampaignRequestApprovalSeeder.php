<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DirectCampaignRequestApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $advertiser = User::updateOrCreate(
            ['email' => 'direct.review@adshqip.com'],
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
                'last_name' => 'Review',
                'company_name' => 'Direct Campaign Review Demo',
                'phone' => '+355690001111',
                'country_code' => 'AL',
                'website_url' => 'https://adshqip.com',
                'currency' => 'EUR',
                'balance' => 0,
            ]
        );

        $campaigns = [
            [
                'name' => 'Direct Campaign Review 001',
                'brand_name' => 'Travel Boost Balkans',
                'pricing_model' => 'cpm',
                'marketing_objective' => 'brand_awareness',
                'bid_amount' => 2.1000,
                'daily_budget' => 140.0000,
                'total_budget' => 2100.0000,
                'remaining_budget' => 2100.0000,
                'status' => 'pending_review',
                'admin_approved' => false,
                'rejection_reason' => null,
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'Direct Campaign Review 002',
                'brand_name' => 'Kosovo App Push',
                'pricing_model' => 'cpc',
                'marketing_objective' => 'traffic',
                'bid_amount' => 0.7200,
                'daily_budget' => 85.0000,
                'total_budget' => 1100.0000,
                'remaining_budget' => 1100.0000,
                'status' => 'pending_review',
                'admin_approved' => false,
                'rejection_reason' => null,
                'created_at' => now()->subDays(1),
            ],
            [
                'name' => 'Direct Campaign Review 003',
                'brand_name' => 'Commerce Video Lift',
                'pricing_model' => 'cpv',
                'marketing_objective' => 'video_views',
                'bid_amount' => 0.1500,
                'daily_budget' => 90.0000,
                'total_budget' => 1500.0000,
                'remaining_budget' => 900.0000,
                'status' => 'active',
                'admin_approved' => true,
                'rejection_reason' => null,
                'created_at' => now()->subDays(10),
            ],
            [
                'name' => 'Direct Campaign Review 004',
                'brand_name' => 'Lead Gen Native Demo',
                'pricing_model' => 'cpa',
                'marketing_objective' => 'lead_generation',
                'bid_amount' => 4.0000,
                'daily_budget' => 70.0000,
                'total_budget' => 980.0000,
                'remaining_budget' => 980.0000,
                'status' => 'rejected',
                'admin_approved' => false,
                'rejection_reason' => 'Landing page and targeting need revision.',
                'created_at' => now()->subDays(8),
            ],
        ];

        foreach ($campaigns as $campaign) {
            DB::table('aq_direct_campaigns')->updateOrInsert(
                [
                    'advertiser_id' => $advertiser->id,
                    'name' => $campaign['name'],
                ],
                [
                    'description' => 'Seeded direct campaign request approval record for the admin approvals page.',
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
                    'destination_url' => 'https://adshqip.com/direct-review-demo',
                    'display_url' => 'adshqip.com/direct-review-demo',
                    'headline' => $campaign['brand_name'],
                    'body_text' => 'Sample direct campaign seeded for review approval workflow testing.',
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
