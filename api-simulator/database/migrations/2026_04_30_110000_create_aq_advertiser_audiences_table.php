<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_advertiser_audiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index();
            $table->string('name', 160);
            $table->string('slug', 180);
            $table->string('type', 40)->default('custom')->index();
            $table->string('status', 40)->default('active')->index();
            $table->string('source', 60)->default('manual')->index();
            $table->text('description')->nullable();
            $table->json('countries')->nullable();
            $table->json('devices')->nullable();
            $table->json('interests')->nullable();
            $table->json('keywords')->nullable();
            $table->json('rules')->nullable();
            $table->unsignedInteger('estimated_size')->default(0);
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();

            $table->unique(['advertiser_id', 'slug'], 'uk_advertiser_audience_slug');
            $table->foreign('advertiser_id', 'fk_advertiser_audiences_user')
                ->references('id')
                ->on('aq_users')
                ->cascadeOnDelete();
        });

        Schema::create('aq_campaign_audience', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->index();
            $table->unsignedBigInteger('audience_id')->index();
            $table->string('mode', 20)->default('include')->index();
            $table->timestamps();

            $table->unique(['campaign_id', 'audience_id'], 'uk_campaign_audience');
            $table->foreign('campaign_id', 'fk_campaign_audience_campaign')
                ->references('id')
                ->on('aq_campaigns')
                ->cascadeOnDelete();
            $table->foreign('audience_id', 'fk_campaign_audience_audience')
                ->references('id')
                ->on('aq_advertiser_audiences')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_campaign_audience');
        Schema::dropIfExists('aq_advertiser_audiences');
    }
};
