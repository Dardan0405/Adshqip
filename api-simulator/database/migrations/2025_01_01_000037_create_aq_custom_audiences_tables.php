<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_audience_pixels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users');
            $table->string('pixel_name', 255)->comment('e.g. "Main Website Pixel"');
            $table->char('pixel_uuid', 36)->unique('uk_pixel_uuid')->comment('Unique pixel identifier');
            $table->enum('pixel_type', ['javascript','image','server_to_server'])->default('javascript');
            $table->string('domain', 255)->nullable();
            $table->json('allowed_domains')->nullable();
            $table->text('tag_snippet')->nullable();
            $table->json('events_tracked')->nullable();
            $table->json('custom_events')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->dateTime('last_fired_at')->nullable();
            $table->unsignedBigInteger('total_events')->default(0);
            $table->enum('status', ['active','paused','deleted'])->default('active')->index('idx_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_ap_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_custom_audiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users');
            $table->string('name', 255)->comment('e.g. "Cart Abandoners - Last 30 Days"');
            $table->text('description')->nullable();
            $table->enum('audience_type', ['website_visitors','customer_list','app_activity','engagement','conversion','lookalike','combined'])->index('idx_type');
            $table->unsignedBigInteger('source_pixel_id')->nullable()->index('idx_pixel');
            $table->unsignedBigInteger('source_audience_id')->nullable();
            // Membership settings
            $table->unsignedInteger('membership_duration_days')->default(30);
            $table->unsignedBigInteger('member_count')->default(0);
            $table->dateTime('member_count_updated_at')->nullable();
            $table->unsignedInteger('min_size_for_delivery')->default(100);
            // Data source settings
            $table->unsignedInteger('data_retention_days')->default(180);
            $table->boolean('consent_required')->default(true);
            $table->enum('pii_hashing_algorithm', ['sha256','md5','none'])->default('sha256');
            // Auto-refresh
            $table->boolean('auto_refresh_enabled')->default(true);
            $table->dateTime('last_refreshed_at')->nullable();
            $table->unsignedInteger('refresh_frequency_hours')->default(24);
            // Status
            $table->enum('status', ['building','ready','too_small','paused','error','archived'])->default('building')->index('idx_status');
            $table->boolean('is_shared')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_ca_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('source_pixel_id', 'fk_ca_pixel')->references('id')->on('aq_audience_pixels')->onDelete('set null');
            $table->foreign('source_audience_id', 'fk_ca_source_audience')->references('id')->on('aq_custom_audiences')->onDelete('set null');
        });

        Schema::create('aq_audience_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audience_id')->comment('FK → aq_custom_audiences');
            $table->enum('identifier_type', ['cookie_id','device_id','hashed_email','hashed_phone','idfa','gaid','ip_hash','custom_id']);
            $table->string('identifier_value', 255)->comment('Hashed identifier value');
            $table->enum('source', ['pixel','upload','api','rule','lookalike','engagement'])->default('pixel')->index('idx_source');
            $table->dateTime('first_seen_at')->useCurrent();
            $table->dateTime('last_seen_at')->useCurrent();
            $table->unsignedInteger('event_count')->default(1);
            $table->dateTime('expires_at')->nullable()->index('idx_expires');
            $table->boolean('consent_given')->default(true);
            $table->dateTime('consent_timestamp')->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['audience_id', 'identifier_type', 'identifier_value'], 'uk_audience_identifier');
            $table->index(['identifier_type', 'identifier_value'], 'idx_identifier');
            $table->foreign('audience_id', 'fk_am_audience')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
        });

        Schema::create('aq_audience_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audience_id')->index('idx_audience')->comment('FK → aq_custom_audiences');
            $table->string('rule_name', 255);
            $table->unsignedInteger('rule_group')->default(0)->comment('Rules in same group use OR logic; groups use AND logic');
            // Event matching
            $table->string('event_type', 100)->index('idx_event')->comment('Pixel event name');
            $table->enum('url_match_type', ['exact','contains','starts_with','regex','any'])->default('any');
            $table->string('url_match_value', 2000)->nullable();
            // Additional conditions
            $table->json('condition_params')->nullable();
            $table->unsignedInteger('condition_frequency')->nullable();
            $table->unsignedInteger('condition_recency_days')->nullable();
            // Exclusion
            $table->boolean('is_exclusion')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('audience_id', 'fk_ar_audience')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
        });

        Schema::create('aq_audience_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audience_id')->index('idx_audience')->comment('FK → aq_custom_audiences');
            $table->enum('sync_type', ['csv_upload','crm_sync','api_push','manual','pixel_backfill']);
            $table->string('file_name', 500)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->enum('identifier_type', ['hashed_email','hashed_phone','device_id','cookie_id','custom_id'])->default('hashed_email');
            $table->unsignedInteger('total_records')->nullable();
            $table->unsignedInteger('matched_records')->nullable();
            $table->unsignedInteger('failed_records')->nullable();
            $table->unsignedInteger('duplicate_records')->nullable();
            $table->text('error_log')->nullable();
            $table->enum('status', ['pending','processing','completed','failed','cancelled'])->default('pending')->index('idx_status');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->foreign('audience_id', 'fk_as_audience')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
        });

        Schema::create('aq_campaign_audience_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedBigInteger('audience_id')->index('idx_audience')->comment('FK → aq_custom_audiences');
            $table->enum('match_mode', ['include','exclude'])->default('include')->index('idx_mode');
            $table->decimal('bid_adjustment_pct', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['campaign_id', 'campaign_source', 'audience_id'], 'uk_campaign_audience');
            $table->foreign('audience_id', 'fk_caa_audience')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
        });

        Schema::create('aq_audience_lookalikes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lookalike_audience_id')->index('idx_lookalike')->comment('FK → aq_custom_audiences');
            $table->unsignedBigInteger('seed_audience_id')->index('idx_seed')->comment('FK → aq_custom_audiences');
            $table->unsignedInteger('seed_size')->nullable();
            $table->decimal('expansion_ratio', 4, 2)->default(1.00);
            $table->decimal('similarity_threshold', 5, 4)->nullable();
            $table->json('target_countries')->nullable();
            $table->enum('model_type', ['behavioral','demographic','interest','combined'])->default('combined');
            $table->string('model_version', 50)->nullable();
            $table->decimal('model_quality_score', 5, 2)->nullable();
            $table->unsignedBigInteger('estimated_reach')->nullable();
            $table->enum('generation_status', ['pending','processing','ready','failed','expired'])->default('pending')->index('idx_status');
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('lookalike_audience_id', 'fk_al_lookalike')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
            $table->foreign('seed_audience_id', 'fk_al_seed')->references('id')->on('aq_custom_audiences')->onDelete('cascade');
        });

        Schema::create('aq_pixel_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pixel_id')->index('idx_pixel')->comment('FK → aq_audience_pixels');
            $table->string('event_name', 100)->index('idx_event');
            $table->dateTime('event_timestamp')->useCurrent()->index('idx_timestamp');
            // Visitor identification
            $table->string('cookie_id', 255)->nullable()->index('idx_cookie');
            $table->string('device_id', 255)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            // Event context
            $table->string('page_url', 2000)->nullable();
            $table->string('referrer_url', 2000)->nullable();
            $table->decimal('event_value', 12, 2)->nullable();
            $table->string('event_currency', 3)->nullable();
            $table->json('event_params')->nullable();
            // Geo & device
            $table->char('country_code', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('device_type', ['desktop','mobile','tablet','smart_tv','other'])->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            // Processing
            $table->boolean('is_processed')->default(false)->index('idx_processed');
            $table->json('audiences_matched')->nullable();

            $table->foreign('pixel_id', 'fk_pe_pixel')->references('id')->on('aq_audience_pixels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_pixel_events');
        Schema::dropIfExists('aq_audience_lookalikes');
        Schema::dropIfExists('aq_campaign_audience_assoc');
        Schema::dropIfExists('aq_audience_syncs');
        Schema::dropIfExists('aq_audience_rules');
        Schema::dropIfExists('aq_audience_members');
        Schema::dropIfExists('aq_custom_audiences');
        Schema::dropIfExists('aq_audience_pixels');
    }
};
