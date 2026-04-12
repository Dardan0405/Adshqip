<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;
use App\Models\StatDaily;
use Carbon\Carbon;

class CampaignReportSeeder extends Seeder
{
    /**
     * Seed campaign performance stats for the Campaign Reports page.
     * Populates aq_stats_daily with campaign_id-linked rows covering
     * impressions, unique_impressions, clicks, unique_clicks,
     * conversions, revenue, ctr, ecpm for the last 6 months.
     */
    public function run(): void
    {
        $this->command->info('Starting Campaign Report Seeder...');

        $campaigns = Campaign::with('advertiser')->get();

        if ($campaigns->isEmpty()) {
            $this->command->warn('No campaigns found. Please seed campaigns first.');
            return;
        }

        $this->command->info("Found {$campaigns->count()} campaigns.");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($campaigns as $campaign) {
            $label = "{$campaign->name} (advertiser_id: {$campaign->advertiser_id})";
            $this->command->info("Seeding stats for: {$label}");

            // Active campaigns get more days; paused/pending get fewer
            $minDays = match ($campaign->status ?? '') {
                'active'  => 18,
                'paused'  => 5,
                default   => 10,
            };
            $maxDays = match ($campaign->status ?? '') {
                'active'  => 25,
                'paused'  => 12,
                default   => 18,
            };

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                $numDays     = rand($minDays, min($maxDays, $daysInMonth));
                $days        = array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date = $currentMonth->copy()->day($dayNum)->format('Y-m-d');

                    $impressions       = rand(2000, 40000);
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    $ctrPct            = rand(80, 500) / 100;           // 0.8 % – 5 %
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    $conversions       = (int) round($clicks * (rand(1, 5) / 100));
                    $cpm               = rand(150, 1800) / 100;         // €1.50 – €18.00
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    $ecpm              = $impressions > 0
                        ? round(($revenue / $impressions) * 1000, 4)
                        : 0;

                    StatDaily::updateOrCreate(
                        [
                            'date'          => $date,
                            'campaign_id'   => $campaign->id,
                            'advertiser_id' => $campaign->advertiser_id,
                            'publisher_id'  => null,
                            'zone_id'       => $campaign->zone_id,
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
        $this->command->info('Campaign Report Seeder completed successfully!');
        $this->command->info('Seeded 6 months of daily performance stats per campaign.');
    }
}
