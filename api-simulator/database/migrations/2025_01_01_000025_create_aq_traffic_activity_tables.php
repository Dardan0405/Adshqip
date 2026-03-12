<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_traffic_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('slug', 50)->unique('uk_slug');
            $table->text('description')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
        });

        Schema::create('aq_campaign_traffic_source', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedInteger('traffic_source_id');
            $table->boolean('is_allowed')->default(true)->comment('1=whitelist, 0=blacklist');
            $table->primary(['campaign_id', 'traffic_source_id']);
            $table->foreign('campaign_id', 'fk_cts_campaign')->references('id')->on('aq_campaigns')->onDelete('cascade');
            $table->foreign('traffic_source_id', 'fk_cts_source')->references('id')->on('aq_traffic_sources')->onDelete('cascade');
        });

        Schema::create('aq_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_user');
            $table->string('action', 100)->index('idx_action')->comment('e.g. campaign.created, ad.approved, payout.processed');
            $table->string('entity_type', 50)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->json('metadata')->nullable()->comment('Additional context');
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->index(['entity_type', 'entity_id'], 'idx_entity');
        });

        Schema::create('aq_log_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('log_enabled')->default(true);
            $table->boolean('log_account_activity')->default(true);
            $table->boolean('log_campaign_activity')->default(true);
            $table->boolean('log_ad_activity')->default(true);
            $table->boolean('log_payment_activity')->default(true);
            $table->integer('retention_days')->default(90);
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_log_settings');
        Schema::dropIfExists('aq_activity_log');
        Schema::dropIfExists('aq_campaign_traffic_source');
        Schema::dropIfExists('aq_traffic_sources');
    }
};
