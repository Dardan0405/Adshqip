<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\StatDaily;
use Carbon\Carbon;

class SiteReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Site Report Seeder...');

        $sites = Site::where('is_deleted', false)->with('publisher')->get();

        if ($sites->isEmpty()) {
            $this->command->warn('No sites found. Please seed sites first.');
            return;
        }

        $this->command->info("Found {$sites->count()} sites.");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($sites as $site) {
            $this->command->info("Seeding stats for site: {$site->name} ({$site->domain})");

            $minDays = match ($site->status ?? '') { 'active' => 15, default => 8 };
            $maxDays = match ($site->status ?? '') { 'active' => 25, default => 15 };

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                $numDays     = rand($minDays, min($maxDays, $daysInMonth));
                $days        = array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date              = $currentMonth->copy()->day($dayNum)->format('Y-m-d');
                    $impressions       = rand(2000, 50000);
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    $ctrPct            = rand(80, 480) / 100;
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    $conversions       = (int) round($clicks * (rand(1, 5) / 100));
                    $cpm               = rand(100, 1400) / 100;
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    $publisherEarnings = round($revenue * (rand(55, 70) / 100), 4);
                    $ecpm              = $impressions > 0 ? round(($publisherEarnings / $impressions) * 1000, 4) : 0;

                    StatDaily::updateOrCreate(
                        [
                            'date'         => $date,
                            'site_id'      => $site->id,
                            'publisher_id' => $site->publisher_id,
                            'campaign_id'  => null,
                            'ad_id'        => null,
                            'zone_id'      => null,
                        ],
                        [
                            'impressions'        => $impressions,
                            'unique_impressions'  => $uniqueImpressions,
                            'clicks'             => $clicks,
                            'unique_clicks'      => $uniqueClicks,
                            'conversions'        => $conversions,
                            'revenue'            => $revenue,
                            'publisher_earnings' => $publisherEarnings,
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

        $this->command->info('Site Report Seeder completed successfully!');
    }
}
