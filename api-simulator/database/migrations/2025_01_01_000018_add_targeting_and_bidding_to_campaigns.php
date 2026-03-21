<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->json('targeting_region')->nullable()->after('targeting_schedule')->comment('Region targeting (continents)');
            $table->json('traffic_sources')->nullable()->after('targeting_region')->comment('Traffic source bidding config');
            $table->json('country_bids')->nullable()->after('traffic_sources')->comment('Country-level bid overrides');
            $table->json('ad_formats')->nullable()->after('country_bids')->comment('Selected ad format types');
        });
    }

    public function down(): void
    {
        Schema::table('aq_campaigns', function (Blueprint $table) {
            $table->dropColumn(['targeting_region', 'traffic_sources', 'country_bids', 'ad_formats']);
        });
    }
};
