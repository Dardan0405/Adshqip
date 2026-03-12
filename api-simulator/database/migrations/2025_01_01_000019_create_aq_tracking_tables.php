<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_impressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index('idx_ad');
            $table->unsignedBigInteger('zone_id')->nullable()->index('idx_zone');
            $table->unsignedBigInteger('campaign_id')->nullable()->index('idx_campaign');
            $table->string('viewer_id', 64)->index('idx_viewer')->comment('old: viewer_id (cookie/fingerprint)');
            $table->string('fingerprint_id', 64)->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->char('country_code', 2)->nullable()->index('idx_country');
            $table->string('region', 100)->nullable();
            $table->enum('device_type', ['desktop','mobile','tablet'])->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('referer_url', 2000)->nullable();
            $table->decimal('cost', 10, 6)->nullable()->comment('CPM cost for this impression');
            $table->boolean('is_viewable')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });

        Schema::create('aq_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index('idx_ad');
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable()->index('idx_campaign');
            $table->unsignedBigInteger('impression_id')->nullable();
            $table->string('viewer_id', 64)->index('idx_viewer');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->enum('device_type', ['desktop','mobile','tablet'])->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->decimal('cost', 10, 6)->nullable()->comment('CPC cost for this click');
            $table->boolean('is_unique')->default(true);
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });

        Schema::create('aq_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->unsignedBigInteger('click_id')->nullable()->index('idx_click');
            $table->string('viewer_id', 64);
            $table->enum('conversion_type', ['sale','lead','signup','install','custom'])->default('sale');
            $table->decimal('revenue', 12, 4)->nullable();
            $table->decimal('payout', 12, 4)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_conversions');
        Schema::dropIfExists('aq_clicks');
        Schema::dropIfExists('aq_impressions');
    }
};
