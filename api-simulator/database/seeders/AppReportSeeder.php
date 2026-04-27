<?php

namespace Database\Seeders;

use App\Models\TelegramMiniApp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AppReportSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Ensure publisher user exists ──────────────────────────────────
        $publisher = User::updateOrCreate(
            ['email' => 'publisher@adshqip.com'],
            [
                'password_hash'      => Hash::make('password123'),
                'role'               => 'publisher',
                'status'             => 'active',
                'preferred_language' => 'sq',
                'theme_preference'   => 'dark',
                'timezone'           => 'Europe/Tirane',
                'is_deleted'         => false,
            ]
        );

        // ── 2. Create the iOS app ─────────────────────────────────────────────
        $app = TelegramMiniApp::updateOrCreate(
            ['app_short_name' => 'adshqip_ios_app'],
            [
                'user_id'          => $publisher->id,
                'application_type' => 'ios',
                'app_name'         => 'Adshqip iOS App',
                'bot_username'     => 'AdshqipiOSAppBot',
                'bot_token_hash'   => Hash::make('adshqip-ios-bot-token'),
                'app_url'          => 'https://apps.apple.com/app/adshqip/id1234567890',
                'icon_url'         => null,
                'description'      => 'Native iOS publisher app for Adshqip ad monetization.',
                'category'         => 'monetization',
                'status'           => 'active',
                'admin_approved'   => true,
                'is_deleted'       => false,
                'total_sessions'   => 0,
                'total_events'     => 0,
            ]
        );

        $this->command->info("iOS App ID: {$app->id} — {$app->app_name}");

        // ── 3. Clear old seeded events for this app ───────────────────────────
        DB::table('aq_telegram_mini_app_events')->where('mini_app_id', $app->id)->delete();

        // ── 4. Seed 30 days of ad events ─────────────────────────────────────
        $totalImpressions = 0;
        $totalClicks      = 0;
        $totalEvents      = 0;

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->toDateString();

            $dayImpressions = rand(800, 3500);
            $dayClicks      = (int) ($dayImpressions * (rand(15, 45) / 1000)); // 1.5%–4.5% CTR
            $cpmRate        = rand(100, 400) / 100;   // €1.00–€4.00 eCPM
            $revenuePerImp  = $cpmRate / 1000;

            // --- impression events (batched in 100s) ---
            $impressionBatch = [];
            for ($i = 0; $i < $dayImpressions; $i++) {
                $hour   = rand(0, 23);
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $impressionBatch[] = [
                    'mini_app_id'    => $app->id,
                    'session_id'     => null,
                    'telegram_user_id' => rand(100000000, 999999999),
                    'event_type'     => 'ad_impression',
                    'event_name'     => 'banner_viewed',
                    'event_data'     => null,
                    'revenue'        => round($revenuePerImp, 4),
                    'currency'       => 'EUR',
                    'created_at'     => "{$date} {$hour}:{$minute}:{$second}",
                ];

                if (count($impressionBatch) === 100) {
                    DB::table('aq_telegram_mini_app_events')->insert($impressionBatch);
                    $impressionBatch = [];
                }
            }
            if (! empty($impressionBatch)) {
                DB::table('aq_telegram_mini_app_events')->insert($impressionBatch);
            }

            // --- click events ---
            $clickBatch = [];
            for ($i = 0; $i < $dayClicks; $i++) {
                $hour   = rand(0, 23);
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $clickBatch[] = [
                    'mini_app_id'    => $app->id,
                    'session_id'     => null,
                    'telegram_user_id' => rand(100000000, 999999999),
                    'event_type'     => 'ad_click',
                    'event_name'     => 'banner_clicked',
                    'event_data'     => null,
                    'revenue'        => 0.0000,
                    'currency'       => 'EUR',
                    'created_at'     => "{$date} {$hour}:{$minute}:{$second}",
                ];
            }
            if (! empty($clickBatch)) {
                DB::table('aq_telegram_mini_app_events')->insert($clickBatch);
            }

            $totalImpressions += $dayImpressions;
            $totalClicks      += $dayClicks;
            $totalEvents      += $dayImpressions + $dayClicks;

            $this->command->line("  {$date} — {$dayImpressions} impressions, {$dayClicks} clicks");
        }

        // ── 5. Update denormalised counters on the app ────────────────────────
        $app->update([
            'total_sessions' => (int) ($totalImpressions / rand(3, 8)),
            'total_events'   => $totalEvents,
            'last_active_at' => now(),
        ]);

        $this->command->info("Done. {$totalImpressions} impressions + {$totalClicks} clicks seeded for app ID {$app->id}.");
    }
}
