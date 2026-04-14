<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RtbDataSeeder extends Seeder
{
    private const AD_EXCHANGES = [
        ['name' => 'Google Ad Exchange', 'type' => 'DSP', 'endpoint_url' => 'https://adx.google.com/bid', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'AppNexus', 'type' => 'DSP', 'endpoint_url' => 'https://ib.adnxs.com/openrtb2', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'The Trade Desk', 'type' => 'DSP', 'endpoint_url' => 'https://bid.thetradedesk.com/bid', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'MediaMath', 'type' => 'DSP', 'endpoint_url' => 'https://bid.mediamath.com/ortb', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'Criteo', 'type' => 'DSP', 'endpoint_url' => 'https://rtb.criteo.com/bid', 'auction_currency' => 'EUR', 'status' => 'active'],
        ['name' => 'Amazon DSP', 'type' => 'DSP', 'endpoint_url' => 'https://aax.amazon.com/bid', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'Verizon Media', 'type' => 'DSP', 'endpoint_url' => 'https://rtb.oath.com/bid', 'auction_currency' => 'USD', 'status' => 'testing'],
        ['name' => 'Rubicon Project', 'type' => 'SSP', 'endpoint_url' => 'https://fastlane.rubiconproject.com/a/api', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'PubMatic', 'type' => 'SSP', 'endpoint_url' => 'https://hbopenbid.pubmatic.com/translator', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'Index Exchange', 'type' => 'SSP', 'endpoint_url' => 'https://htlb.casalemedia.com/cygnus', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'OpenX', 'type' => 'SSP', 'endpoint_url' => 'https://rtb.openx.net/w/1.0/arj', 'auction_currency' => 'USD', 'status' => 'active'],
        ['name' => 'Smaato', 'type' => 'ad_network', 'endpoint_url' => 'https://prebid.ad.smaato.net/oapi/prebid', 'auction_currency' => 'EUR', 'status' => 'active'],
        ['name' => 'AdColony', 'type' => 'ad_network', 'endpoint_url' => 'https://adc3-launch.adcolony.com/v4/bid', 'auction_currency' => 'USD', 'status' => 'inactive'],
        ['name' => 'Unity Ads', 'type' => 'ad_network', 'endpoint_url' => 'https://auction.unityads.unity3d.com/v4/bid', 'auction_currency' => 'USD', 'status' => 'testing'],
        ['name' => 'IronSource', 'type' => 'ad_network', 'endpoint_url' => 'https://rtb.ironsrc.com/bid', 'auction_currency' => 'USD', 'status' => 'active'],
    ];

    private const COUNTRIES = ['US', 'GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'PL', 'BR', 'CA', 'AU', 'JP', 'IN', 'MX', 'TR'];
    private const DEVICE_TYPES = ['desktop', 'mobile', 'tablet', 'tv'];
    private const AD_FORMATS = ['banner', 'video', 'native', 'interstitial', 'rewarded'];
    private const SIZES = [
        ['width' => 300, 'height' => 250],
        ['width' => 728, 'height' => 90],
        ['width' => 320, 'height' => 50],
        ['width' => 160, 'height' => 600],
        ['width' => 300, 'height' => 600],
        ['width' => 970, 'height' => 250],
    ];

    public function run(): void
    {
        $this->command->info('Seeding RTB/DSP data...');

        // Clear existing data
        DB::table('aq_rtb_bid_responses')->truncate();
        DB::table('aq_rtb_bid_requests')->truncate();
        DB::table('aq_ad_exchanges')->truncate();

        // Seed Ad Exchanges
        $this->command->info('Creating ad exchanges...');
        $exchangeIds = [];
        foreach (self::AD_EXCHANGES as $exchange) {
            $id = DB::table('aq_ad_exchanges')->insertGetId([
                'name' => $exchange['name'],
                'type' => $exchange['type'],
                'endpoint_url' => $exchange['endpoint_url'],
                'auth_type' => 'api_key',
                'credentials' => json_encode(['api_key' => Str::random(32)]),
                'auction_currency' => $exchange['auction_currency'],
                'auction_type' => rand(1, 2),
                'is_strict_openrtb' => true,
                'status' => $exchange['status'],
                'created_at' => now()->subDays(rand(30, 180)),
                'updated_at' => now(),
            ]);
            $exchangeIds[] = $id;
        }

        // Seed Bid Requests and Responses
        $this->command->info('Creating bid requests and responses...');
        $totalRequests = 0;
        $totalResponses = 0;

        // Generate data for last 30 days
        for ($day = 30; $day >= 0; $day--) {
            $date = now()->subDays($day);

            // Random number of requests per day (500-2000)
            $requestsPerDay = rand(500, 2000);

            $requests = [];
            $responses = [];

            for ($i = 0; $i < $requestsPerDay; $i++) {
                $requestId = Str::uuid()->toString();
                $exchangeId = $exchangeIds[array_rand($exchangeIds)];
                $size = self::SIZES[array_rand(self::SIZES)];
                $bidFloor = round(rand(10, 500) / 100, 4); // $0.10 - $5.00

                $createdAt = $date->copy()->addSeconds(rand(0, 86399));

                $requests[] = [
                    'request_id' => $requestId,
                    'exchange_id' => $exchangeId,
                    'zone_id' => rand(1, 50),
                    'bid_floor' => $bidFloor,
                    'ad_format' => self::AD_FORMATS[array_rand(self::AD_FORMATS)],
                    'width' => $size['width'],
                    'height' => $size['height'],
                    'country_code' => self::COUNTRIES[array_rand(self::COUNTRIES)],
                    'device_type' => self::DEVICE_TYPES[array_rand(self::DEVICE_TYPES)],
                    'user_agent' => $this->randomUserAgent(),
                    'ip_address' => $this->randomIp(),
                    'response_time_ms' => rand(5, 150),
                    'status' => $this->randomStatus(),
                    'created_at' => $createdAt,
                ];

                // 60-80% chance of getting a response
                if (rand(1, 100) <= rand(60, 80)) {
                    $bidPrice = round($bidFloor + (rand(0, 200) / 100), 4);
                    $win = rand(1, 100) <= 35; // 35% win rate

                    $responses[] = [
                        'request_id' => $requestId,
                        'exchange_id' => $exchangeId,
                        'bid_price' => $bidPrice,
                        'ad_markup' => '<div class="ad-creative">Ad Creative ' . Str::random(8) . '</div>',
                        'creative_id' => 'cr_' . Str::random(12),
                        'advertiser_domain' => $this->randomDomain(),
                        'win' => $win,
                        'win_price' => $win ? round($bidPrice * (rand(80, 95) / 100), 4) : null,
                        'created_at' => $createdAt->copy()->addMilliseconds(rand(10, 100)),
                    ];
                    $totalResponses++;
                }

                $totalRequests++;
            }

            // Batch insert for performance
            foreach (array_chunk($requests, 500) as $chunk) {
                DB::table('aq_rtb_bid_requests')->insert($chunk);
            }

            foreach (array_chunk($responses, 500) as $chunk) {
                DB::table('aq_rtb_bid_responses')->insert($chunk);
            }

            if ($day % 5 === 0) {
                $this->command->info("  Processed day -$day...");
            }
        }

        $this->command->info("Created {$totalRequests} bid requests");
        $this->command->info("Created {$totalResponses} bid responses");
        $this->command->info('RTB/DSP data seeding completed!');
    }

    private function randomStatus(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 70) return 'responded';
        if ($rand <= 85) return 'sent';
        if ($rand <= 95) return 'timeout';
        return 'error';
    }

    private function randomUserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        ];
        return $agents[array_rand($agents)];
    }

    private function randomIp(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254);
    }

    private function randomDomain(): string
    {
        $domains = [
            'example-advertiser.com',
            'brand-showcase.net',
            'premium-ads.io',
            'advert-network.com',
            'marketing-hub.org',
            'digital-promo.co',
            'adspace-pro.com',
            'click-media.net',
        ];
        return $domains[array_rand($domains)];
    }
}
