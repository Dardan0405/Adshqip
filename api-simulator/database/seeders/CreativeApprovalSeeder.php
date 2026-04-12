<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\Campaign;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreativeApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $advertiser = User::updateOrCreate(
            ['email' => 'creative.approvals@adshqip.com'],
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
                'first_name' => 'Creative',
                'last_name' => 'Advertiser',
                'company_name' => 'Creative Approval Demo',
                'country_code' => 'AL',
                'website_url' => 'https://adshqip.com',
                'currency' => 'EUR',
                'balance' => 0,
            ]
        );

        $campaign = Campaign::updateOrCreate(
            ['name' => 'Creative Approval Demo Campaign', 'advertiser_id' => $advertiser->id],
            [
                'description' => 'Seeded campaign used for creative approval workflow testing.',
                'marketing_objective' => 'traffic',
                'campaign_type' => 'cpm',
                'status' => 'active',
                'bid_amount' => 1.2500,
                'daily_budget' => 120.0000,
                'total_budget' => 2500.0000,
                'remaining_budget' => 2500.0000,
                'currency' => 'EUR',
                'start_date' => now()->subDays(14),
                'end_date' => now()->addDays(30),
                'frequency_cap' => 3,
                'frequency_cap_period' => 'day',
                'targeting_geo' => ['countries' => ['AL', 'XK']],
                'targeting_device' => ['desktop', 'mobile'],
                'weight' => 5,
                'admin_approved' => true,
                'is_deleted' => false,
            ]
        );

        $ads = [
            [
                'name' => 'Creative Approval Demo Banner',
                'ad_type' => 'image',
                'status' => 'pending_review',
                'destination_url' => 'https://adshqip.com/demo/banner',
                'display_url' => 'adshqip.com/demo/banner',
                'headline' => 'Creative Banner Demo',
                'body_text' => 'Seeded banner ad waiting for approval.',
                'call_to_action' => 'Learn More',
                'brand_name' => 'Creative Approval Demo',
                'admin_approved' => false,
                'creative' => [
                    'file_path' => '/uploads/creatives/1773956394_22_captcha-banner-300x250.png',
                    'file_type' => 'image',
                    'mime_type' => 'image/png',
                    'file_size_bytes' => 148000,
                    'width' => 300,
                    'height' => 250,
                    'alt_text' => 'Creative Approval Demo Banner',
                ],
            ],
            [
                'name' => 'Creative Approval Demo Sidebar',
                'ad_type' => 'image',
                'status' => 'active',
                'destination_url' => 'https://adshqip.com/demo/sidebar',
                'display_url' => 'adshqip.com/demo/sidebar',
                'headline' => 'Creative Sidebar Demo',
                'body_text' => 'Seeded sidebar ad already approved.',
                'call_to_action' => 'Shop Now',
                'brand_name' => 'Creative Approval Demo',
                'admin_approved' => true,
                'creative' => [
                    'file_path' => '/uploads/creatives/1773956488_23_sidebar-skyscraper-160x600.webp',
                    'file_type' => 'image',
                    'mime_type' => 'image/webp',
                    'file_size_bytes' => 224000,
                    'width' => 160,
                    'height' => 600,
                    'alt_text' => 'Creative Approval Demo Sidebar',
                ],
            ],
            [
                'name' => 'Creative Approval Demo Video',
                'ad_type' => 'video',
                'status' => 'rejected',
                'destination_url' => 'https://adshqip.com/demo/video',
                'display_url' => 'adshqip.com/demo/video',
                'headline' => 'Creative Video Demo',
                'body_text' => 'Seeded video ad rejected for review flow testing.',
                'call_to_action' => 'Watch Now',
                'brand_name' => 'Creative Approval Demo',
                'admin_approved' => false,
                'creative' => [
                    'file_path' => '/uploads/creatives/1774442638_210_thumb.png',
                    'file_type' => 'video',
                    'mime_type' => 'video/mp4',
                    'file_size_bytes' => 1480000,
                    'width' => 1280,
                    'height' => 720,
                    'duration_seconds' => 15,
                    'alt_text' => 'Creative Approval Demo Video',
                    'thumbnail_path' => '/uploads/creatives/1774442638_210_thumb.png',
                    'video_url' => '/uploads/creatives/1774442638_210_out-stream.mp4',
                ],
            ],
        ];

        foreach ($ads as $definition) {
            $ad = Ad::updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'name' => $definition['name'],
                ],
                [
                    'ad_type' => $definition['ad_type'],
                    'status' => $definition['status'],
                    'destination_url' => $definition['destination_url'],
                    'display_url' => $definition['display_url'],
                    'headline' => $definition['headline'],
                    'body_text' => $definition['body_text'],
                    'call_to_action' => $definition['call_to_action'],
                    'brand_name' => $definition['brand_name'],
                    'admin_approved' => $definition['admin_approved'],
                    'is_deleted' => false,
                ]
            );

            AdCreative::updateOrCreate(
                [
                    'ad_id' => $ad->id,
                    'is_primary' => true,
                ],
                [
                    'file_path' => $definition['creative']['file_path'],
                    'video_url' => $definition['creative']['video_url'] ?? null,
                    'file_type' => $definition['creative']['file_type'],
                    'mime_type' => $definition['creative']['mime_type'],
                    'file_size_bytes' => $definition['creative']['file_size_bytes'],
                    'width' => $definition['creative']['width'],
                    'height' => $definition['creative']['height'],
                    'duration_seconds' => $definition['creative']['duration_seconds'] ?? null,
                    'alt_text' => $definition['creative']['alt_text'],
                    'thumbnail_path' => $definition['creative']['thumbnail_path'] ?? null,
                    'is_primary' => true,
                ]
            );
        }
    }
}
