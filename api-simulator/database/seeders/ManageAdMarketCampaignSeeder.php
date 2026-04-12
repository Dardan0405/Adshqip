<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManageAdMarketCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $advertisers = [
            [
                'email' => 'admarket.alpha@adshqip.com',
                'status' => 'active',
                'profile' => [
                    'first_name' => 'Arben',
                    'last_name' => 'Krasniqi',
                    'company_name' => 'Alpha Growth Media',
                    'country_code' => 'XK',
                    'website_url' => 'https://alpha-growth.test',
                ],
                'campaigns' => [
                    [
                        'name' => 'Alpha Finance Push',
                        'campaign_type' => 'cpc',
                        'marketing_objective' => 'traffic',
                        'status' => 'active',
                        'bid_amount' => 0.8500,
                        'daily_budget' => 120.0000,
                        'total_budget' => 2400.0000,
                        'remaining_budget' => 1680.0000,
                        'admin_approved' => true,
                    ],
                    [
                        'name' => 'Alpha App Install Burst',
                        'campaign_type' => 'cpa',
                        'marketing_objective' => 'app_installs',
                        'status' => 'paused',
                        'bid_amount' => 2.4000,
                        'daily_budget' => 90.0000,
                        'total_budget' => 1800.0000,
                        'remaining_budget' => 920.0000,
                        'admin_approved' => true,
                    ],
                ],
            ],
            [
                'email' => 'admarket.beta@adshqip.com',
                'status' => 'active',
                'profile' => [
                    'first_name' => 'Besa',
                    'last_name' => 'Hoxha',
                    'company_name' => 'Beta Commerce Lab',
                    'country_code' => 'AL',
                    'website_url' => 'https://beta-commerce.test',
                ],
                'campaigns' => [
                    [
                        'name' => 'Beta Retail Awareness',
                        'campaign_type' => 'cpm',
                        'marketing_objective' => 'brand_awareness',
                        'status' => 'active',
                        'bid_amount' => 1.6500,
                        'daily_budget' => 150.0000,
                        'total_budget' => 3000.0000,
                        'remaining_budget' => 2200.0000,
                        'admin_approved' => true,
                    ],
                    [
                        'name' => 'Beta Summer Reengagement',
                        'campaign_type' => 'cpv',
                        'marketing_objective' => 'engagement',
                        'status' => 'pending_review',
                        'bid_amount' => 0.1800,
                        'daily_budget' => 70.0000,
                        'total_budget' => 1250.0000,
                        'remaining_budget' => 1250.0000,
                        'admin_approved' => false,
                    ],
                    [
                        'name' => 'Beta Coupon Sprint',
                        'campaign_type' => 'cpc',
                        'marketing_objective' => 'conversions',
                        'status' => 'active',
                        'bid_amount' => 0.7400,
                        'daily_budget' => 95.0000,
                        'total_budget' => 2100.0000,
                        'remaining_budget' => 1450.0000,
                        'admin_approved' => true,
                    ],
                ],
            ],
            [
                'email' => 'admarket.gamma@adshqip.com',
                'status' => 'suspended',
                'profile' => [
                    'first_name' => 'Gent',
                    'last_name' => 'Berisha',
                    'company_name' => 'Gamma Performance Studio',
                    'country_code' => 'MK',
                    'website_url' => 'https://gamma-performance.test',
                ],
                'campaigns' => [
                    [
                        'name' => 'Gamma Lead Engine',
                        'campaign_type' => 'cpa',
                        'marketing_objective' => 'lead_generation',
                        'status' => 'paused',
                        'bid_amount' => 3.1000,
                        'daily_budget' => 110.0000,
                        'total_budget' => 2600.0000,
                        'remaining_budget' => 1735.0000,
                        'admin_approved' => true,
                    ],
                ],
            ],
        ];

        foreach ($advertisers as $definition) {
            $advertiser = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'advertiser',
                    'status' => $definition['status'],
                    'preferred_language' => 'en',
                    'theme_preference' => 'system',
                    'timezone' => 'Europe/Tirane',
                    'is_deleted' => false,
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $advertiser->id],
                [
                    'first_name' => $definition['profile']['first_name'],
                    'last_name' => $definition['profile']['last_name'],
                    'company_name' => $definition['profile']['company_name'],
                    'country_code' => $definition['profile']['country_code'],
                    'website_url' => $definition['profile']['website_url'],
                    'currency' => 'EUR',
                    'balance' => 0,
                ]
            );

            foreach ($definition['campaigns'] as $index => $campaign) {
                Campaign::updateOrCreate(
                    [
                        'advertiser_id' => $advertiser->id,
                        'name' => $campaign['name'],
                    ],
                    [
                        'description' => 'Seeded campaign for the Manage AdMarket Campaign admin page.',
                        'format_id' => null,
                        'marketing_objective' => $campaign['marketing_objective'],
                        'campaign_type' => $campaign['campaign_type'],
                        'status' => $campaign['status'],
                        'bid_amount' => $campaign['bid_amount'],
                        'daily_budget' => $campaign['daily_budget'],
                        'total_budget' => $campaign['total_budget'],
                        'remaining_budget' => $campaign['remaining_budget'],
                        'currency' => 'EUR',
                        'start_date' => now()->subDays(10 + $index),
                        'end_date' => now()->addDays(20 + $index),
                        'frequency_cap' => 3,
                        'frequency_cap_period' => 'day',
                        'targeting_geo' => ['countries' => ['AL', 'XK', 'MK']],
                        'targeting_device' => ['desktop', 'mobile'],
                        'weight' => 5,
                        'admin_approved' => $campaign['admin_approved'],
                        'is_deleted' => false,
                    ]
                );
            }
        }
    }
}
