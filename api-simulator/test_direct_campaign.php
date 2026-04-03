<?php
// Quick test script — run with: php artisan tinker < test_direct_campaign.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DirectCampaign;
use App\Models\DirectCampaignStat;

echo "=== Direct Campaign Serve Engine Test ===\n\n";

// 1. Find the campaign
$campaign = DirectCampaign::where('is_deleted', false)->first();
if (!$campaign) {
    echo "ERROR: No direct campaigns found!\n";
    exit(1);
}

echo "Campaign found:\n";
echo "  ID:          {$campaign->id}\n";
echo "  Name:        {$campaign->name}\n";
echo "  Status:      {$campaign->status}\n";
echo "  Pricing:     {$campaign->pricing_model}\n";
echo "  Bid:         {$campaign->bid_amount}\n";
echo "  Budget:      {$campaign->total_budget}\n";
echo "  Remaining:   {$campaign->remaining_budget}\n";
echo "  Destination: {$campaign->destination_url}\n";
echo "  Headline:    {$campaign->headline}\n";
echo "  Body:        " . substr($campaign->body_text ?? '', 0, 60) . "\n";
echo "  CTA:         {$campaign->call_to_action}\n";
echo "  Creatives:   {$campaign->creatives->count()}\n";
echo "  Targeting:   {$campaign->targeting->count()} rules\n\n";

// 2. Check stats BEFORE
$statsBefore = DirectCampaignStat::where('campaign_id', $campaign->id)->sum('impressions');
echo "Stats BEFORE test: {$statsBefore} impressions\n\n";

// 3. Make sure campaign is active for test
if ($campaign->status !== 'active') {
    echo "Setting campaign status to 'active' for testing...\n";
    $campaign->status = 'active';
    $campaign->save();
}

echo "=== Endpoints to test ===\n";
echo "  SERVE:      http://127.0.0.1:8000/serve/direct/{$campaign->id}\n";
echo "  CLICK:      http://127.0.0.1:8000/serve/direct/{$campaign->id}/click\n";
echo "  VIEW:       http://127.0.0.1:8000/serve/direct/{$campaign->id}/view\n";
echo "  CONVERSION: http://127.0.0.1:8000/serve/direct/{$campaign->id}/conversion\n";
echo "  DEBUG:      http://127.0.0.1:8000/serve/direct/{$campaign->id}?debug=1\n";
echo "  POSTBACK:   http://127.0.0.1:8000/track/direct/{$campaign->id}/postback?click_id=test123\n";
echo "\nDone.\n";
