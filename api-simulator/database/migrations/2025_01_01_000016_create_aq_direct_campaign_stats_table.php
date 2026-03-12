<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_campaign_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index('idx_date');
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->unsignedBigInteger('creative_id')->nullable()->index('idx_creative');
            $table->unsignedBigInteger('zone_id')->nullable()->index('idx_zone');
            $table->char('country_code', 2)->nullable()->index('idx_country');
            $table->enum('device_type', ['desktop','mobile','tablet'])->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('viewable_impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('revenue', 12, 4)->default(0.0000)->comment('Advertiser spend');
            $table->decimal('publisher_earnings', 12, 4)->default(0.0000);
            $table->decimal('ecpm', 8, 4)->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('conversion_rate', 8, 4)->nullable();
            $table->decimal('fill_rate', 5, 2)->nullable();
            $table->decimal('avg_cpc', 10, 4)->nullable();
            $table->decimal('avg_cpa', 10, 4)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['date','campaign_id','creative_id','zone_id','country_code','device_type'], 'uk_daily_dc_combo');
            $table->foreign('campaign_id', 'fk_dcs_campaign')->references('id')->on('aq_direct_campaigns')->onDelete('cascade');
            $table->foreign('creative_id', 'fk_dcs_creative')->references('id')->on('aq_direct_campaign_creatives')->onDelete('set null');
            $table->foreign('zone_id', 'fk_dcs_zone')->references('id')->on('aq_zones')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaign_stats');
    }
};
