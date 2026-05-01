<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ad_serving_logs', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_type', 40)->default('network');
            $table->string('event_type', 40);
            $table->string('status', 30)->default('served');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->unsignedBigInteger('direct_campaign_id')->nullable();
            $table->unsignedBigInteger('direct_creative_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->unsignedBigInteger('advertiser_id')->nullable();
            $table->string('request_id', 80)->nullable();
            $table->string('viewer_id', 80)->nullable();
            $table->string('click_id', 80)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('pricing_model', 30)->nullable();
            $table->decimal('bid_amount', 12, 4)->nullable();
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('publisher_earnings', 12, 4)->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('request_url', 1000)->nullable();
            $table->string('destination_url', 1000)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
            $table->index(['delivery_type', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['campaign_id', 'created_at']);
            $table->index(['direct_campaign_id', 'created_at']);
            $table->index(['zone_id', 'created_at']);
            $table->index(['publisher_id', 'created_at']);
            $table->index(['advertiser_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_ad_serving_logs');
    }
};
