<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Campaign;
use App\Models\Site;
use App\Models\UrlAdReport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SiteUrlReportSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('aq_url_ad_reports')) {
            $this->command?->warn('aq_url_ad_reports table is missing. Run migrations first.');
            return;
        }

        if (!Schema::hasColumn('aq_url_ad_reports', 'referrer_url')) {
            $this->command?->warn('aq_url_ad_reports.referrer_url is missing. Run migrations first.');
            return;
        }

        $ad = $this->adForReport();
        $zones = $this->zonesForReport();

        if (!$ad || $zones->isEmpty()) {
            $this->command?->warn('Unable to seed Site URL Report data. Missing ad or zone data.');
            return;
        }

        UrlAdReport::query()
            ->where('request_url', 'like', '%seed-site-url-report%')
            ->delete();

        $referrers = [
            'https://news.example.com/politics/election-live',
            'https://news.example.com/sports/match-preview',
            'https://finance.example.net/markets/daily-brief',
            'https://lifestyle.example.org/travel/balkan-guide',
            'https://tech.example.io/reviews/mobile-devices',
            null,
        ];

        $rows = [];
        $now = now();

        foreach ($zones as $zoneIndex => $zone) {
            foreach ($referrers as $referrerIndex => $referrer) {
                $impressions = 80 + ($zoneIndex * 35) + ($referrerIndex * 18);
                $clicks = max(3, (int) round($impressions * (0.025 + ($referrerIndex * 0.004))));
                $conversions = max(1, (int) floor($clicks * 0.18));

                $rows = array_merge(
                    $rows,
                    $this->eventRows($ad, $zone, 'serve', $impressions, $referrer, $now),
                    $this->eventRows($ad, $zone, 'click', $clicks, $referrer, $now),
                    $this->eventRows($ad, $zone, 'conversion', $conversions, $referrer, $now),
                );
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            UrlAdReport::insert($chunk);
        }

        $this->command?->info('Seeded ' . count($rows) . ' Site URL Report events for advertiser #' . $ad->campaign?->advertiser_id . '.');
    }

    private function adForReport(): ?Ad
    {
        $ad = Ad::query()
            ->with('campaign')
            ->where('is_deleted', false)
            ->whereHas('campaign', fn ($query) => $query->where('is_deleted', false))
            ->orderByDesc('id')
            ->first();

        if ($ad) {
            return $ad;
        }

        $advertiser = User::firstOrCreate(
            ['email' => 'site-url-advertiser@example.com'],
            [
                'password_hash' => Hash::make('password'),
                'role' => 'advertiser',
                'status' => 'active',
                'email_verified_at' => now(),
                'is_deleted' => false,
            ]
        );

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Seeded Site URL Campaign',
            'marketing_objective' => 'traffic',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1.25,
            'daily_budget' => 50,
            'total_budget' => 1000,
            'remaining_budget' => 1000,
            'currency' => 'EUR',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(30),
            'is_deleted' => false,
        ]);

        return Ad::create([
            'campaign_id' => $campaign->id,
            'name' => 'Seeded Site URL Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://advertiser.example.com/landing',
            'display_url' => 'advertiser.example.com',
            'admin_approved' => true,
            'weight' => 5,
            'is_deleted' => false,
        ]);
    }

    private function zonesForReport()
    {
        $zones = Zone::query()
            ->with('site')
            ->where('is_deleted', false)
            ->whereHas('site', fn ($query) => $query->where('is_deleted', false))
            ->limit(4)
            ->get();

        if ($zones->isNotEmpty()) {
            return $zones;
        }

        $publisher = User::firstOrCreate(
            ['email' => 'site-url-publisher@example.com'],
            [
                'password_hash' => Hash::make('password'),
                'role' => 'publisher',
                'status' => 'active',
                'email_verified_at' => now(),
                'is_deleted' => false,
            ]
        );

        $site = Site::create([
            'publisher_id' => $publisher->id,
            'name' => 'Seeded News Site',
            'domain' => 'news.example.com',
            'description' => 'Seed data for advertiser Site URL Report.',
            'category' => 'news',
            'language' => 'en',
            'monthly_pageviews' => 250000,
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $zoneData = [
            'site_id' => $site->id,
            'name' => 'Seeded Header Banner',
            'placement' => 'header',
            'floor_price' => 0.5,
            'status' => 'active',
            'is_deleted' => false,
        ];

        if (Schema::hasColumn('aq_zones', 'format_key')) {
            $zoneData['format_key'] = 'display_web';
        }

        Zone::create($zoneData);

        return Zone::query()->with('site')->where('site_id', $site->id)->get();
    }

    private function eventRows(Ad $ad, Zone $zone, string $eventType, int $count, ?string $referrer, $now): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $createdAt = $now->copy()->subDays($i % 14)->subMinutes(($i * 7) % 1440);

            $rows[] = [
                'ad_id' => $ad->id,
                'campaign_id' => $ad->campaign_id,
                'direct_campaign_id' => null,
                'direct_creative_id' => null,
                'zone_id' => $zone->id,
                'event_type' => $eventType,
                'request_url' => url('/seed-site-url-report/' . $eventType),
                'referrer_url' => $referrer,
                'tracking_url' => url('/serve/ad/' . $ad->id . '/' . $eventType),
                'destination_url' => $ad->destination_url,
                'device_type' => ['desktop', 'mobile', 'tablet'][$i % 3],
                'ip_address' => '203.0.113.' . (($i % 200) + 1),
                'user_agent' => 'SiteUrlReportSeeder/1.0',
                'url_hidden' => false,
                'url_encoded' => false,
                'created_at' => $createdAt,
            ];
        }

        return $rows;
    }
}
