<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ad;
use App\Models\StatDaily;
use Carbon\Carbon;

class CreativeReportSeeder extends Seeder
{
    /**
     * Seed creative (ad) performance stats for the Creative Reports page.
     * Populates aq_stats_daily with ad_id-linked rows covering
     * impressions, unique_impressions, clicks, unique_clicks,
     * conversions, revenue, ctr, ecpm for the last 6 months.
     */
    public function run(): void
    {
        $this->command->info('Starting Creative Report Seeder...');

        $ads = Ad::with('campaign')->where('is_deleted', false)->get();

        if ($ads->isEmpty()) {
            $this->command->warn('No ads found. Please seed ads first.');
            return;
        }

        $this->command->info("Found {$ads->count()} ads (creatives).");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($ads as $ad) {
            $label = "{$ad->name} (campaign_id: {$ad->campaign_id})";
            $this->command->info("Seeding stats for: {$label}");

            // Active ads get more days; others get fewer
            $minDays = match ($ad->status ?? '') {
                'active'  => 15,
                'paused'  => 4,
                default   => 8,
            };
            $maxDays = match ($ad->status ?? '') {
                'active'  => 25,
                'paused'  => 10,
                default   => 16,
            };

            $advertiserId = $ad->campaign?->advertiser_id;
            $zoneId       = $ad->campaign?->zone_id;

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                $numDays     = rand($minDays, min($maxDays, $daysInMonth));
                $days        = array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date = $currentMonth->copy()->day($dayNum)->format('Y-m-d');

                    $impressions       = rand(1000, 30000);
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    $ctrPct            = rand(80, 550) / 100;           // 0.8 % – 5.5 %
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    $conversions       = (int) round($clicks * (rand(1, 6) / 100));
                    $cpm               = rand(100, 2000) / 100;         // €1.00 – €20.00
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    $ecpm              = $impressions > 0
                        ? round(($revenue / $impressions) * 1000, 4)
                        : 0;

                    StatDaily::updateOrCreate(
                        [
                            'date'          => $date,
                            'ad_id'         => $ad->id,
                            'campaign_id'   => $ad->campaign_id,
                            'advertiser_id' => $advertiserId,
                            'publisher_id'  => null,
                            'zone_id'       => $zoneId,
                        ],
                        [
                            'impressions'        => $impressions,
                            'unique_impressions'  => $uniqueImpressions,
                            'clicks'             => $clicks,
                            'unique_clicks'      => $uniqueClicks,
                            'conversions'        => $conversions,
                            'revenue'            => $revenue,
                            'publisher_earnings' => round($revenue * 0.6, 4),
                            'ecpm'               => $ecpm,
                            'ctr'                => $ctrPct,
                            'fill_rate'          => rand(75, 99),
                        ]
                    );
                }

                $this->command->line("  + {$currentMonth->format('Y-m')}: " . count($days) . " days seeded");
                $currentMonth->addMonth();
            }
        }

        $this->command->info('');
        $this->command->info('Creative Report Seeder completed successfully!');
        $this->command->info('Seeded 6 months of daily performance stats per ad creative.');
    }
}
