<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_url_ad_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ad_id')->nullable()->index();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->unsignedBigInteger('direct_campaign_id')->nullable()->index();
            $table->unsignedBigInteger('direct_creative_id')->nullable()->index();
            $table->unsignedBigInteger('zone_id')->nullable()->index();
            $table->string('event_type', 50)->index();
            $table->text('request_url')->nullable();
            $table->text('tracking_url')->nullable();
            $table->text('destination_url')->nullable();
            $table->string('device_type', 20)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->boolean('url_hidden')->default(false);
            $table->boolean('url_encoded')->default(false);
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_url_ad_reports');
    }
};
