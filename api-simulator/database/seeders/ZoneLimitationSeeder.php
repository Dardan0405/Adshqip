<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Zone;
use App\Models\ZoneLimitation;
use Illuminate\Database\Seeder;

class ZoneLimitationSeeder extends Seeder
{
    public function run(): void
    {
        $advertisers = User::where('role', 'advertiser')->get();
        $zones = Zone::where('is_deleted', false)->pluck('id')->toArray();

        if ($advertisers->isEmpty() || empty($zones)) {
            $this->command->info('No advertisers or zones found. Skipping ZoneLimitationSeeder.');
            return;
        }

        foreach ($advertisers as $advertiser) {
            // Create a whitelist entry
            $whitelistZones = array_slice($zones, 0, min(3, count($zones)));
            ZoneLimitation::updateOrCreate(
                [
                    'advertiser_id' => $advertiser->id,
                    'name' => 'Premium Zones Whitelist',
                    'type' => 'adblock_whitelist',
                ],
                [
                    'zone_ids' => $whitelistZones,
                ]
            );

            // Create a blacklist entry
            $blacklistZones = array_slice($zones, -min(2, count($zones)));
            ZoneLimitation::updateOrCreate(
                [
                    'advertiser_id' => $advertiser->id,
                    'name' => 'Low Quality Zones Blacklist',
                    'type' => 'adblock_blacklist',
                ],
                [
                    'zone_ids' => $blacklistZones,
                ]
            );

            // Create another whitelist
            if (count($zones) > 3) {
                ZoneLimitation::updateOrCreate(
                    [
                        'advertiser_id' => $advertiser->id,
                        'name' => 'Video Zones Whitelist',
                        'type' => 'adblock_whitelist',
                    ],
                    [
                        'zone_ids' => array_slice($zones, 1, 2),
                    ]
                );
            }
        }
    }
}
