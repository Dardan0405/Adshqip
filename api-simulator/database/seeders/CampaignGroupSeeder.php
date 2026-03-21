<?php

namespace Database\Seeders;

use App\Models\CampaignGroup;
use Illuminate\Database\Seeder;

class CampaignGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'id' => 1,
                'name' => 'Q1 2025 Campaigns',
                'description' => 'First quarter promotional campaigns',
                'status' => 'active',
                'campaign_count' => 0,
                'color' => 'blue',
            ],
            [
                'id' => 2,
                'name' => 'Brand Awareness',
                'description' => 'Brand building and awareness campaigns',
                'status' => 'active',
                'campaign_count' => 0,
                'color' => 'purple',
            ],
            [
                'id' => 3,
                'name' => 'Performance Marketing',
                'description' => 'Direct response and conversion campaigns',
                'status' => 'active',
                'campaign_count' => 0,
                'color' => 'green',
            ],
            [
                'id' => 4,
                'name' => 'Retargeting',
                'description' => 'Retargeting and remarketing campaigns',
                'status' => 'active',
                'campaign_count' => 0,
                'color' => 'orange',
            ],
            [
                'id' => 5,
                'name' => 'Seasonal Promotions',
                'description' => 'Holiday and seasonal campaign group',
                'status' => 'paused',
                'campaign_count' => 0,
                'color' => 'red',
            ],
        ];

        foreach ($groups as $group) {
            CampaignGroup::create($group);
        }
    }
}
