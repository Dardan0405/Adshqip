<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestCampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the advertiser
        $advertiser = User::where('email', 'advertiser@adshqip.com')
            ->where('role', 'advertiser')
            ->first();

        if (!$advertiser) {
            $this->command->error('Advertiser with email advertiser@adshqip.com not found!');
            return;
        }

        $this->command->info("Creating test campaigns for advertiser: {$advertiser->email} (ID: {$advertiser->id})");

        // Create Network Campaigns with smaller budgets
        $campaigns = [
            [
                'name' => 'Summer Sale 2026',
                'status' => 'active',
                'daily_budget' => 15.00,
                'total_budget' => 300.00,
            ],
            [
                'name' => 'Brand Awareness Campaign',
                'status' => 'active',
                'daily_budget' => 25.00,
                'total_budget' => 500.00,
            ],
            [
                'name' => 'Product Launch - New Collection',
                'status' => 'active',
                'daily_budget' => 30.00,
                'total_budget' => 750.00,
            ],
            [
                'name' => 'Spring Promotion 2026',
                'status' => 'active',
                'daily_budget' => 10.00,
                'total_budget' => 200.00,
            ],
            [
                'name' => 'Mobile App Campaign',
                'status' => 'active',
                'daily_budget' => 20.00,
                'total_budget' => 400.00,
            ],
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::create([
                'advertiser_id' => $advertiser->id,
                'name' => $campaignData['name'],
                'status' => $campaignData['status'],
                'daily_budget' => $campaignData['daily_budget'],
                'total_budget' => $campaignData['total_budget'],
                'is_deleted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("  ✓ Created campaign: {$campaignData['name']}");
        }

        $this->command->info("\n✅ Successfully created " . count($campaigns) . " campaigns!");
    }
}
