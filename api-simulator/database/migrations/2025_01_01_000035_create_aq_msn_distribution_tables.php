<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_distribution_networks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 50)->unique('uk_slug');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('network_type', ['owned','partner','exchange','msn','social'])->default('partner')->index('idx_type');
            $table->string('logo_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('api_endpoint', 500)->nullable();
            $table->string('api_key_encrypted', 500)->nullable();
            $table->json('supported_formats')->nullable();
            $table->json('supported_countries')->nullable();
            $table->decimal('min_bid_cpm', 10, 4)->nullable();
            $table->decimal('revenue_share_pct', 5, 2)->nullable();
            $table->boolean('is_premium')->default(false);
            $table->enum('brand_safety_level', ['basic','standard','strict'])->default('standard');
            $table->boolean('requires_approval')->default(false);
            $table->enum('status', ['active','inactive','testing','deprecated'])->default('active')->index('idx_status');
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_campaign_network_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedInteger('network_id')->index('idx_network');
            $table->boolean('is_exclusive')->default(false)->index('idx_exclusive');
            $table->decimal('bid_adjustment_pct', 5, 2)->nullable();
            $table->decimal('daily_budget_cap', 12, 4)->nullable();
            $table->integer('frequency_cap')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['campaign_id', 'campaign_source', 'network_id'], 'uk_campaign_network');
            $table->foreign('network_id', 'fk_cna_network')->references('id')->on('aq_distribution_networks')->onDelete('cascade');
        });

        Schema::create('aq_msn_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 80)->unique('uk_slug');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('property_type', ['homepage','news','finance','sports','entertainment','lifestyle','weather','email','search','edge_start','edge_newtab','app'])->index('idx_type');
            $table->string('url', 500)->nullable();
            $table->json('supported_formats')->nullable();
            $table->json('supported_sizes')->nullable();
            $table->unsignedBigInteger('avg_daily_impressions')->nullable();
            $table->decimal('avg_cpm', 10, 4)->nullable();
            $table->json('audience_demographics')->nullable();
            $table->enum('brand_safety_tier', ['tier_1','tier_2','tier_3'])->default('tier_1');
            $table->json('geo_availability')->nullable();
            $table->boolean('is_premium')->default(true);
            $table->enum('status', ['active','inactive','coming_soon'])->default('active')->index('idx_status');
            $table->integer('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_msn_campaign_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            // Property targeting
            $table->json('target_properties')->nullable();
            $table->json('exclude_properties')->nullable();
            // Content category targeting
            $table->json('content_categories')->nullable();
            $table->enum('content_category_mode', ['include','exclude'])->default('include');
            // MSN audience targeting
            $table->json('msn_audience_segments')->nullable();
            $table->json('linkedin_profile_targeting')->nullable();
            // Brand safety
            $table->enum('brand_safety_level', ['standard','strict','custom'])->default('strict')->index('idx_brand_safety');
            $table->json('blocked_content_types')->nullable();
            // Viewability & quality
            $table->unsignedTinyInteger('viewability_threshold_pct')->default(50);
            $table->boolean('above_the_fold_only')->default(false);
            // MSN native ad customization
            $table->string('msn_headline_override', 255)->nullable();
            $table->text('msn_body_override')->nullable();
            $table->string('msn_thumbnail_url', 500)->nullable();
            $table->string('msn_sponsored_label', 50)->default('Ad');
            // Performance settings
            $table->boolean('auto_optimize_placements')->default(true);
            $table->decimal('max_cpm_override', 10, 4)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['campaign_id', 'campaign_source'], 'uk_campaign_source');
        });

        Schema::create('aq_msn_performance_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index('idx_date');
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedInteger('property_id')->index('idx_property');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('views')->default(0)->comment('Video/CTW completed views');
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('spend', 12, 4)->default(0.0000);
            $table->decimal('revenue', 12, 4)->default(0.0000);
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('vcr', 8, 4)->nullable()->comment('Video completion rate %');
            $table->decimal('viewability_rate', 8, 4)->nullable();
            $table->decimal('avg_cpm', 10, 4)->nullable();
            $table->decimal('avg_cpc', 10, 4)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['date', 'campaign_id', 'campaign_source', 'property_id'], 'uk_daily_campaign_property');
            $table->foreign('property_id', 'fk_mps_property')->references('id')->on('aq_msn_properties')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_msn_performance_stats');
        Schema::dropIfExists('aq_msn_campaign_settings');
        Schema::dropIfExists('aq_msn_properties');
        Schema::dropIfExists('aq_campaign_network_assoc');
        Schema::dropIfExists('aq_distribution_networks');
    }
};
