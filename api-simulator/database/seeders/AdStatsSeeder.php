<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\StatDaily;
use Illuminate\Database\Seeder;

class AdStatsSeeder extends Seeder
{
    public function run(): void
    {
        $ads = Ad::with('campaign.zone.site')->where('is_deleted', false)->get();

        if ($ads->isEmpty()) {
            $this->command->warn('No ads found. Please run AdCreativeSeeder first.');
            return;
        }

        $countries = ['US', 'GB', 'DE', 'FR', 'CA', 'AU', 'NL', 'ES', 'IT', 'BR'];
        $devices = ['desktop', 'mobile', 'tablet'];

        $totalRows = 0;

        foreach ($ads as $ad) {
            $zoneId = $ad->campaign?->zone_id;
            $siteId = $ad->campaign?->zone?->site_id;
            $publisherId = $ad->campaign?->zone?->site?->publisher_id;

            // Generate 30 days of stats per ad
            for ($daysAgo = 0; $daysAgo < 30; $daysAgo++) {
                $date = now()->subDays($daysAgo)->format('Y-m-d');

                // Randomize traffic volume based on ad status
                $multiplier = match ($ad->status) {
                    'active' => rand(80, 150) / 100,
                    'paused' => rand(5, 20) / 100,
                    default => rand(10, 40) / 100,
                };

                $impressions = (int) (rand(500, 15000) * $multiplier);
                $uniqueImpressions = (int) ($impressions * (rand(60, 85) / 100));
                $clickRate = rand(8, 45) / 1000; // 0.8% - 4.5% CTR
                $clicks = max(0, (int) ($impressions * $clickRate));
                $uniqueClicks = (int) ($clicks * (rand(70, 90) / 100));
                $conversionRate = rand(10, 80) / 1000; // 1% - 8% of clicks
                $conversions = max(0, (int) ($clicks * $conversionRate));
                $cpmRate = rand(150, 2500) / 100; // $1.50 - $25.00 eCPM
                $revenue = round(($impressions / 1000) * $cpmRate, 4);
                $publisherEarnings = round($revenue * (rand(55, 70) / 100), 4);
                $ecpm = $impressions > 0 ? round(($revenue / $impressions) * 1000, 4) : 0;
                $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : 0;
                $viewable = (int) ($impressions * (rand(55, 85) / 100));

                $country = $countries[array_rand($countries)];
                $device = $devices[array_rand($devices)];

                StatDaily::create([
                    'date' => $date,
                    'ad_id' => $ad->id,
                    'campaign_id' => $ad->campaign_id,
                    'zone_id' => $zoneId,
                    'site_id' => $siteId,
                    'advertiser_id' => $ad->campaign?->advertiser_id,
                    'publisher_id' => $publisherId,
                    'country_code' => $country,
                    'device_type' => $device,
                    'impressions' => $impressions,
                    'unique_impressions' => $uniqueImpressions,
                    'clicks' => $clicks,
                    'unique_clicks' => $uniqueClicks,
                    'conversions' => $conversions,
                    'viewable_impressions' => $viewable,
                    'revenue' => $revenue,
                    'publisher_earnings' => $publisherEarnings,
                    'ecpm' => $ecpm,
                    'ctr' => $ctr,
                    'fill_rate' => rand(6000, 9800) / 100,
                ]);

                $totalRows++;
            }
        }

        $this->command->info("Created {$totalRows} daily stat rows for " . $ads->count() . ' ads.');
    }
}
