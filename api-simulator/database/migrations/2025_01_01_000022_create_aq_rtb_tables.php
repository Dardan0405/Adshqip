<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_ad_exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->comment('old: exchange_name');
            $table->enum('type', ['DSP','SSP','ad_network'])->default('DSP')->comment('old: type');
            $table->string('endpoint_url', 500)->comment('old: ping_url');
            $table->enum('auth_type', ['api_key','oauth2','basic','none'])->default('api_key');
            $table->json('credentials')->nullable()->comment('Encrypted; old: username/password/authentiction_key');
            $table->string('seller_id', 50)->nullable();
            $table->string('auction_currency', 3)->default('EUR')->comment('old: auction_currency');
            $table->tinyInteger('auction_type')->default(2)->comment('1=first-price, 2=second-price');
            $table->boolean('is_strict_openrtb')->default(true)->comment('old: is_stirct_open_rtb_standard');
            $table->enum('status', ['active','inactive','testing'])->default('active');
            $table->unsignedBigInteger('agency_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_rtb_bid_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 64)->index('idx_request')->comment('Unique OpenRTB request id');
            $table->unsignedInteger('exchange_id')->nullable()->index('idx_exchange');
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->decimal('bid_floor', 10, 4)->default(0.0000);
            $table->string('ad_format', 50)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->enum('status', ['sent','responded','timeout','error'])->default('sent');
            $table->dateTime('created_at')->useCurrent()->index('idx_created');
        });

        Schema::create('aq_rtb_bid_responses', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 64)->index('idx_request');
            $table->unsignedInteger('exchange_id')->nullable()->index('idx_exchange');
            $table->decimal('bid_price', 10, 4)->nullable();
            $table->text('ad_markup')->nullable();
            $table->string('creative_id', 100)->nullable();
            $table->string('advertiser_domain', 255)->nullable();
            $table->boolean('win')->default(false);
            $table->decimal('win_price', 10, 4)->nullable();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_rtb_bid_responses');
        Schema::dropIfExists('aq_rtb_bid_requests');
        Schema::dropIfExists('aq_ad_exchanges');
    }
};
