<?php

namespace Database\Seeders;

use App\Models\PixelTracker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PixelTrackerSeeder extends Seeder
{
    public function run(): void
    {
        $pixels = [
            [
                'id' => 1,
                'name' => 'Purchase Conversion',
                'description' => 'Tracks completed purchases',
                'type' => 'conversion',
                'pixel_code' => 'PX' . strtoupper(Str::random(10)),
                'tracking_url' => 'https://track.adshqip.com/pixel/conversion',
                'is_active' => true,
                'fire_count' => 0,
            ],
            [
                'id' => 2,
                'name' => 'Lead Form Submit',
                'description' => 'Tracks lead form submissions',
                'type' => 'conversion',
                'pixel_code' => 'PX' . strtoupper(Str::random(10)),
                'tracking_url' => 'https://track.adshqip.com/pixel/lead',
                'is_active' => true,
                'fire_count' => 0,
            ],
            [
                'id' => 3,
                'name' => 'Page View Tracker',
                'description' => 'General page view tracking',
                'type' => 'pageview',
                'pixel_code' => 'PX' . strtoupper(Str::random(10)),
                'tracking_url' => 'https://track.adshqip.com/pixel/pageview',
                'is_active' => true,
                'fire_count' => 0,
            ],
            [
                'id' => 4,
                'name' => 'Add to Cart Event',
                'description' => 'Tracks when users add items to cart',
                'type' => 'event',
                'pixel_code' => 'PX' . strtoupper(Str::random(10)),
                'tracking_url' => 'https://track.adshqip.com/pixel/event',
                'parameters' => ['event_name' => 'add_to_cart'],
                'is_active' => true,
                'fire_count' => 0,
            ],
        ];

        foreach ($pixels as $pixel) {
            PixelTracker::create($pixel);
        }
    }
}
