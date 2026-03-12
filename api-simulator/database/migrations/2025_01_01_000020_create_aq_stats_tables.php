<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_stats_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index('idx_date');
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable()->index('idx_campaign');
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('advertiser_id')->nullable()->index('idx_advertiser');
            $table->unsignedBigInteger('publisher_id')->nullable()->index('idx_publisher');
            $table->unsignedInteger('format_id')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->enum('device_type', ['desktop','mobile','tablet'])->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->unsignedBigInteger('viewable_impressions')->default(0);
            $table->decimal('revenue', 12, 4)->default(0.0000)->comment('Advertiser spend');
            $table->decimal('publisher_earnings', 12, 4)->default(0.0000);
            $table->decimal('ecpm', 8, 4)->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('fill_rate', 5, 2)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['date','ad_id','zone_id','country_code','device_type'], 'uk_daily_combo');
        });

        Schema::create('aq_stats_browser', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('browser', 50);
            $table->string('browser_version', 20)->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->index(['date', 'browser'], 'idx_date_browser');
        });

        Schema::create('aq_stats_geo', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->char('country_code', 2);
            $table->string('region', 100)->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('revenue', 12, 4)->default(0.0000);

            $table->index(['date', 'country_code'], 'idx_date_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_stats_geo');
        Schema::dropIfExists('aq_stats_browser');
        Schema::dropIfExists('aq_stats_daily');
    }
};
