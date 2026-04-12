<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zone;
use App\Models\StatDaily;
use Carbon\Carbon;

class AdblockReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting AdBlock Report Seeder...');

        $zones = Zone::where('is_deleted', false)->with('site')->get();

        if ($zones->isEmpty()) {
            $this->command->warn('No zones (adblocks) found. Please seed zones first.');
            return;
        }

        $this->command->info("Found {$zones->count()} adblocks (zones).");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($zones as $zone) {
            $publisherId = $zone->site?->publisher_id;
            $this->command->info("Seeding stats for zone: {$zone->name} (site: {$zone->site?->name})");

            $minDays = match ($zone->status ?? '') { 'active' => 15, default => 8 };
            $maxDays = match ($zone->status ?? '') { 'active' => 25, default => 15 };

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                $numDays     = rand($minDays, min($maxDays, $daysInMonth));
                $days        = array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date              = $currentMonth->copy()->day($dayNum)->format('Y-m-d');
                    $impressions       = rand(1000, 40000);
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    $ctrPct            = rand(80, 500) / 100;
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    $conversions       = (int) round($clicks * (rand(1, 5) / 100));
                    $cpm               = rand(100, 1600) / 100;
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    $publisherEarnings = round($revenue * (rand(55, 70) / 100), 4);
                    $ecpm              = $impressions > 0 ? round(($publisherEarnings / $impressions) * 1000, 4) : 0;

                    StatDaily::updateOrCreate(
                        [
                            'date'         => $date,
                            'zone_id'      => $zone->id,
                            'site_id'      => $zone->site_id,
                            'publisher_id' => $publisherId,
                            'campaign_id'  => null,
                            'ad_id'        => null,
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

        $this->command->info('AdBlock Report Seeder completed successfully!');
    }
}
