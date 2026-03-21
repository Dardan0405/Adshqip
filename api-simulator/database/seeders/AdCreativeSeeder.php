<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class AdCreativeSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing campaigns
        $campaigns = Campaign::where('is_deleted', false)->get();

        if ($campaigns->isEmpty()) {
            $this->command->warn('No campaigns found. Please run CampaignSeeder first.');
            return;
        }

        $adTypes = ['image', 'html', 'video', 'text', 'native', 'rich_media'];
        $statuses = ['active', 'active', 'active', 'paused', 'pending_review'];
        $sizes = [
            ['width' => 300, 'height' => 250, 'name' => 'Medium Rectangle'],
            ['width' => 728, 'height' => 90, 'name' => 'Leaderboard'],
            ['width' => 160, 'height' => 600, 'name' => 'Wide Skyscraper'],
            ['width' => 300, 'height' => 600, 'name' => 'Half Page'],
            ['width' => 320, 'height' => 50, 'name' => 'Mobile Banner'],
            ['width' => 970, 'height' => 250, 'name' => 'Billboard'],
            ['width' => 468, 'height' => 60, 'name' => 'Banner'],
            ['width' => 120, 'height' => 600, 'name' => 'Skyscraper'],
        ];

        $creativeNames = [
            'Summer Sale Banner',
            'Holiday Promo Video',
            'Brand Awareness Native',
            'Product Launch Display',
            'Retargeting Banner A',
            'Mobile App Install',
            'Video Pre-Roll 15s',
            'Rich Media Expandable',
            'Text Ad - Search',
            'Native Feed Card',
            'Interstitial Full Screen',
            'Leaderboard Main',
            'Sidebar Skyscraper',
            'Mobile Interstitial',
            'Video Outstream 30s',
            'HTML5 Animated Banner',
            'Carousel Product Ad',
            'App Promotion Banner',
            'Remarketing Display',
            'Newsletter Sponsor',
        ];

        $destinations = [
            'https://shop.example.com/summer-sale',
            'https://www.brand-example.com/promo',
            'https://landing.example.com/signup',
            'https://app.example.com/download',
            'https://www.example.com/products/new',
            'https://offers.example.com/holiday',
            'https://store.example.com/deals',
            'https://www.example.com/about',
            'https://blog.example.com/article',
            'https://www.example.com/contact',
        ];

        $fileTypes = ['image', 'image', 'image', 'video', 'html5', 'gif'];
        $mimeTypes = [
            'image' => 'image/png',
            'video' => 'video/mp4',
            'html5' => 'text/html',
            'gif' => 'image/gif',
        ];

        foreach ($creativeNames as $index => $name) {
            $campaign = $campaigns->random();
            $adType = $adTypes[array_rand($adTypes)];
            $status = $statuses[array_rand($statuses)];
            $destination = $destinations[array_rand($destinations)];

            $ad = Ad::create([
                'campaign_id' => $campaign->id,
                'name' => $name,
                'ad_type' => $adType,
                'status' => $status,
                'destination_url' => $destination,
                'display_url' => parse_url($destination, PHP_URL_HOST),
                'headline' => $adType === 'text' || $adType === 'native' ? $name . ' - Click Here!' : null,
                'body_text' => $adType === 'text' ? 'Discover amazing deals. Limited time offer. Shop now and save big!' : null,
                'call_to_action' => ['Shop Now', 'Learn More', 'Sign Up', 'Download', 'Get Started'][array_rand(['Shop Now', 'Learn More', 'Sign Up', 'Download', 'Get Started'])],
                'brand_name' => $campaign->name,
                'admin_approved' => $status === 'active',
                'is_deleted' => false,
            ]);

            // Create primary creative
            $size = $sizes[array_rand($sizes)];
            $fileType = $fileTypes[array_rand($fileTypes)];
            if ($adType === 'video') {
                $fileType = 'video';
            } elseif ($adType === 'html' || $adType === 'rich_media') {
                $fileType = 'html5';
            }

            $fileSizeBytes = match ($fileType) {
                'video' => rand(500000, 5000000),
                'html5' => rand(50000, 500000),
                'gif' => rand(100000, 2000000),
                default => rand(10000, 500000),
            };

            AdCreative::create([
                'ad_id' => $ad->id,
                'file_path' => '/uploads/creatives/' . strtolower(str_replace(' ', '-', $name)) . '.' . match ($fileType) {
                    'video' => 'mp4',
                    'html5' => 'html',
                    'gif' => 'gif',
                    default => 'png',
                },
                'file_type' => $fileType,
                'mime_type' => $mimeTypes[$fileType] ?? 'image/png',
                'file_size_bytes' => $fileSizeBytes,
                'width' => $fileType !== 'video' ? $size['width'] : 1920,
                'height' => $fileType !== 'video' ? $size['height'] : 1080,
                'duration_seconds' => $fileType === 'video' ? rand(5, 60) : null,
                'alt_text' => $name,
                'is_primary' => true,
            ]);
        }

        $this->command->info('Created ' . count($creativeNames) . ' ad creatives with primary files.');
    }
}
