<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_dynamic_content_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token_key', 50)->unique('uk_token_key')->comment('Token placeholder key e.g. city, device, weather');
            $table->string('name', 100)->comment('Display name');
            $table->text('description')->nullable();
            $table->enum('category', ['geo','device','time','weather','audience','custom','feed'])->default('custom')->index('idx_category');
            $table->enum('resolver_type', ['geoip','user_agent','server_time','weather_api','audience_data','product_feed','custom_api','static_map'])->comment('How the token value is resolved');
            $table->json('resolver_config')->nullable();
            $table->string('default_value', 255)->nullable();
            $table->string('example_output', 255)->nullable();
            $table->boolean('is_system')->default(true);
            $table->enum('status', ['active','inactive','deprecated'])->default('active');
            $table->dateTime('created_at')->useCurrent();
        });

        Schema::create('aq_dynamic_creative_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users');
            $table->unsignedBigInteger('campaign_id')->nullable()->index('idx_campaign');
            $table->enum('campaign_source', ['rtb','direct'])->nullable();
            $table->enum('asset_type', ['headline','body_text','image','video','cta_text','cta_url','logo','description','display_url','sponsored_label'])->index('idx_type');
            $table->text('content')->comment('The asset value: text string, or URL for images/videos');
            $table->string('language', 5)->default('sq');
            $table->unsignedInteger('character_count')->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('performance_score', 5, 2)->nullable()->index('idx_performance')->comment('Auto-calculated DCO performance score 0-100');
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('ctr', 8, 4)->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_dca_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_dynamic_creative_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->string('rule_name', 255)->comment('Descriptive name');
            $table->integer('priority')->default(0)->index('idx_priority');
            // Conditions
            $table->json('condition_geo_countries')->nullable();
            $table->json('condition_geo_cities')->nullable();
            $table->json('condition_devices')->nullable();
            $table->json('condition_browsers')->nullable();
            $table->json('condition_os')->nullable();
            $table->json('condition_languages')->nullable();
            $table->json('condition_day_of_week')->nullable();
            $table->json('condition_time_range')->nullable();
            $table->json('condition_weather')->nullable();
            $table->json('condition_audience_segments')->nullable();
            $table->json('condition_custom')->nullable();
            // Asset selections
            $table->json('selected_headlines')->nullable();
            $table->json('selected_body_texts')->nullable();
            $table->json('selected_images')->nullable();
            $table->json('selected_videos')->nullable();
            $table->json('selected_ctas')->nullable();
            $table->json('selected_cta_urls')->nullable();
            $table->json('selected_logos')->nullable();
            // Override fields
            $table->string('override_destination_url', 2000)->nullable();
            $table->string('override_display_url', 500)->nullable();
            $table->decimal('override_bid_adjustment_pct', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
        });

        Schema::create('aq_dynamic_product_feeds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser');
            $table->string('name', 255);
            $table->enum('feed_type', ['ecommerce','travel','auto','real_estate','jobs','events','custom'])->default('ecommerce')->index('idx_type');
            $table->enum('source_type', ['manual','csv_upload','xml_url','json_url','google_merchant','facebook_catalog','api'])->default('manual');
            $table->string('source_url', 2000)->nullable();
            $table->json('source_credentials')->nullable();
            $table->unsignedInteger('refresh_interval_minutes')->default(360);
            $table->dateTime('last_fetched_at')->nullable();
            $table->enum('last_fetch_status', ['success','failed','partial','pending'])->nullable();
            $table->text('last_fetch_error')->nullable();
            $table->unsignedInteger('item_count')->default(0);
            $table->json('field_mapping')->nullable();
            $table->string('default_currency', 3)->default('EUR');
            $table->string('default_language', 5)->default('sq');
            $table->json('filter_rules')->nullable();
            $table->enum('status', ['active','paused','error','archived'])->default('active')->index('idx_status');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('advertiser_id', 'fk_dpf_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
        });

        Schema::create('aq_dynamic_product_feed_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feed_id')->comment('FK → aq_dynamic_product_feeds');
            $table->string('external_id', 255)->comment('Product ID from the merchant feed');
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('url', 2000)->comment('Product landing page URL');
            $table->string('image_url', 500)->nullable();
            $table->json('additional_image_urls')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('category', 500)->nullable();
            $table->string('brand', 255)->nullable()->index('idx_brand');
            $table->enum('availability', ['in_stock','out_of_stock','preorder','backorder'])->default('in_stock')->index('idx_availability');
            $table->enum('condition_status', ['new','refurbished','used'])->default('new');
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->json('custom_labels')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->json('geo_availability')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_synced_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['feed_id', 'external_id'], 'uk_feed_external');
            $table->index('price', 'idx_price');
            $table->foreign('feed_id', 'fk_dpfi_feed')->references('id')->on('aq_dynamic_product_feeds')->onDelete('cascade');
        });

        Schema::create('aq_dynamic_budget_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->string('rule_name', 255);
            $table->enum('rule_type', ['bid_increase','bid_decrease','budget_increase','budget_decrease','pause_campaign','resume_campaign','alert_only'])->index('idx_type');
            // Trigger conditions
            $table->enum('trigger_metric', ['ctr','cvr','cpc','cpm','cpa','roas','spend_pct','impressions','clicks','conversions','viewability','frequency']);
            $table->enum('trigger_operator', ['greater_than','less_than','equal_to','between','not_between'])->default('greater_than');
            $table->decimal('trigger_value', 12, 4);
            $table->decimal('trigger_value_upper', 12, 4)->nullable();
            $table->unsignedInteger('trigger_window_minutes')->default(60);
            $table->unsignedInteger('trigger_min_samples')->default(100);
            // Action
            $table->decimal('action_value', 10, 4);
            $table->boolean('action_is_percentage')->default(true);
            $table->decimal('action_cap_min', 10, 4)->nullable();
            $table->decimal('action_cap_max', 10, 4)->nullable();
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->unsignedInteger('max_fires_per_day')->default(10);
            // Notification
            $table->boolean('notify_on_fire')->default(true);
            $table->string('notify_email', 255)->nullable();
            // Metadata
            $table->dateTime('last_fired_at')->nullable();
            $table->unsignedInteger('total_fires')->default(0);
            $table->boolean('is_active')->default(true)->index('idx_active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
        });

        Schema::create('aq_dynamic_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->string('rule_name', 255);
            $table->integer('priority')->default(0)->index('idx_priority');
            // Conditions
            $table->json('condition_geo_countries')->nullable();
            $table->json('condition_geo_cities')->nullable();
            $table->json('condition_devices')->nullable();
            $table->json('condition_os')->nullable();
            $table->json('condition_languages')->nullable();
            $table->json('condition_audience_segments')->nullable();
            $table->json('condition_referrer_domains')->nullable();
            $table->json('condition_utm_params')->nullable();
            $table->json('condition_custom')->nullable();
            // Landing page
            $table->string('destination_url', 2000);
            $table->json('append_params')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
        });

        Schema::create('aq_dynamic_countdown_timers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->string('timer_name', 255);
            $table->dateTime('end_datetime')->index('idx_end');
            $table->string('timezone', 50)->default('Europe/Tirane');
            $table->enum('display_format', ['dhms','hms','days_only','custom'])->default('dhms');
            $table->string('custom_format', 255)->nullable();
            $table->string('before_text', 255)->nullable();
            $table->string('after_text', 255)->nullable();
            $table->enum('expired_action', ['show_after_text','hide_ad','pause_campaign','redirect'])->default('show_after_text');
            $table->string('expired_redirect_url', 2000)->nullable();
            $table->json('style_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
        });

        Schema::create('aq_dynamic_rule_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id')->comment('FK → aq_dynamic_creative_rules or aq_dynamic_budget_rules');
            $table->enum('rule_source', ['creative','budget','landing_page']);
            $table->unsignedBigInteger('campaign_id')->index('idx_campaign');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedBigInteger('impression_id')->nullable();
            $table->json('matched_conditions')->nullable();
            $table->string('action_taken', 255)->nullable();
            $table->string('old_value', 500)->nullable();
            $table->string('new_value', 500)->nullable();
            $table->dateTime('created_at')->useCurrent()->index('idx_created');

            $table->index(['rule_id', 'rule_source'], 'idx_rule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_dynamic_rule_log');
        Schema::dropIfExists('aq_dynamic_countdown_timers');
        Schema::dropIfExists('aq_dynamic_landing_pages');
        Schema::dropIfExists('aq_dynamic_budget_rules');
        Schema::dropIfExists('aq_dynamic_product_feed_items');
        Schema::dropIfExists('aq_dynamic_product_feeds');
        Schema::dropIfExists('aq_dynamic_creative_rules');
        Schema::dropIfExists('aq_dynamic_creative_assets');
        Schema::dropIfExists('aq_dynamic_content_tokens');
    }
};
