<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CampaignGroupSeeder::class,
            PixelTrackerSeeder::class,
            CampaignSeeder::class,
            AdCreativeSeeder::class,
            AdStatsSeeder::class,
            ZoneLimitationSeeder::class,
            BalanceSheetSeeder::class,
            AdvertiserDepositSeeder::class,
            PublisherEarningsSeeder::class,
        ]);
    }
}
