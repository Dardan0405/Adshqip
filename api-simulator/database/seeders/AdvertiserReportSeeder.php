<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StatDaily;
use Carbon\Carbon;

class AdvertiserReportSeeder extends Seeder
{
    /**
     * Seed advertiser performance stats for the Reports page.
     * Populates aq_stats_daily with impressions, unique_impressions,
     * clicks, unique_clicks, conversions, revenue, ctr, ecpm per advertiser.
     */
    public function run(): void
    {
        $this->command->info('Starting Advertiser Report Seeder...');

        $advertisers = User::where('role', 'advertiser')->get();

        if ($advertisers->isEmpty()) {
            $this->command->warn('No advertisers found. Please seed users first.');
            return;
        }

        $this->command->info("Found {$advertisers->count()} advertisers.");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($advertisers as $advertiser) {
            $this->command->info("Seeding report stats for: {$advertiser->email}");

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                // Each advertiser is active 15-25 days per month
                $numDays = rand(15, min(25, $daysInMonth));
                $days    = (array) array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date = $currentMonth->copy()->day($dayNum)->format('Y-m-d');

                    // Raw impressions 3 000 – 50 000
                    $impressions       = rand(3000, 50000);
                    // Unique impressions ≈ 60–85 % of raw
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    // CTR 1–6 %
                    $ctrPct            = rand(100, 600) / 100;
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    // Unique clicks ≈ 70–90 % of clicks
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    // Conversion rate 1–5 % of clicks
                    $conversions       = (int) round($clicks * (rand(1, 5) / 100));
                    // CPM €2–€15 → revenue
                    $cpm               = rand(200, 1500) / 100;
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    // ECPM = (revenue / impressions) * 1000
                    $ecpm              = round(($revenue / $impressions) * 1000, 4);

                    StatDaily::updateOrCreate(
                        [
                            'date'          => $date,
                            'advertiser_id' => $advertiser->id,
                            'campaign_id'   => null,
                            'publisher_id'  => null,
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
                            'fill_rate'          => rand(80, 99),
                        ]
                    );
                }

                $this->command->line("  + {$currentMonth->format('Y-m')}: " . count($days) . " days seeded");
                $currentMonth->addMonth();
            }
        }

        $this->command->info('');
        $this->command->info('Advertiser Report Seeder completed successfully!');
        $this->command->info('Seeded 6 months of daily performance stats per advertiser.');
    }
}
