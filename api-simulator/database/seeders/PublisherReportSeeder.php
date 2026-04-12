<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Site;
use App\Models\StatDaily;
use Carbon\Carbon;

class PublisherReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Publisher Report Seeder...');

        $publishers = User::where('role', 'publisher')->get();

        if ($publishers->isEmpty()) {
            $this->command->warn('No publishers found. Please seed users first.');
            return;
        }

        $this->command->info("Found {$publishers->count()} publishers.");

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate   = Carbon::now()->endOfMonth();

        foreach ($publishers as $publisher) {
            $this->command->info("Seeding stats for: {$publisher->email}");

            // Get one site for this publisher to attach site_id (optional enrichment)
            $site = Site::where('publisher_id', $publisher->id)->where('is_deleted', false)->first();

            $currentMonth = $startDate->copy();

            while ($currentMonth->lte($endDate)) {
                $daysInMonth = $currentMonth->daysInMonth;
                $numDays     = rand(15, min(25, $daysInMonth));
                $days        = array_unique(array_map(fn() => rand(1, $daysInMonth), range(1, $numDays)));
                sort($days);

                foreach ($days as $dayNum) {
                    $date              = $currentMonth->copy()->day($dayNum)->format('Y-m-d');
                    $impressions       = rand(3000, 60000);
                    $uniqueImpressions = (int) round($impressions * (rand(60, 85) / 100));
                    $ctrPct            = rand(80, 500) / 100;
                    $clicks            = (int) round($impressions * $ctrPct / 100);
                    $uniqueClicks      = (int) round($clicks * (rand(70, 90) / 100));
                    $conversions       = (int) round($clicks * (rand(1, 5) / 100));
                    $cpm               = rand(100, 1200) / 100;
                    $revenue           = round(($impressions / 1000) * $cpm, 4);
                    $publisherEarnings = round($revenue * (rand(55, 70) / 100), 4);
                    $ecpm              = $impressions > 0 ? round(($publisherEarnings / $impressions) * 1000, 4) : 0;

                    StatDaily::updateOrCreate(
                        [
                            'date'         => $date,
                            'publisher_id' => $publisher->id,
                            'site_id'      => $site?->id,
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

        $this->command->info('Publisher Report Seeder completed successfully!');
    }
}
