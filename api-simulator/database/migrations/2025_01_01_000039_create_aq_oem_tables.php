<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_oem_manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('OEM brand name e.g. Samsung, Xiaomi, Huawei');
            $table->string('slug', 100)->unique('uk_slug');
            $table->string('logo_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->char('headquarters_country', 2)->nullable()->index('idx_country');
            // Market data
            $table->decimal('global_market_share_pct', 5, 2)->nullable();
            $table->unsignedBigInteger('monthly_active_devices')->nullable();
            $table->json('primary_regions')->nullable();
            $table->json('supported_os')->nullable();
            // Integration
            $table->enum('integration_type', ['api','sdk','s2s','manual'])->default('api');
            $table->string('api_endpoint', 500)->nullable();
            $table->string('api_version', 20)->nullable();
            $table->string('sdk_version', 50)->nullable();
            $table->enum('attribution_method', ['device_id','referrer','s2s_postback','skan','fingerprint'])->default('device_id');
            $table->boolean('supports_retargeting')->default(false);
            $table->boolean('supports_deep_linking')->default(false);
            // Commercial terms
            $table->enum('pricing_model', ['cpi','cpc','cpm','cpa','revenue_share','flat_fee','hybrid'])->default('cpi');
            $table->decimal('min_bid_cpi', 10, 4)->nullable();
            $table->decimal('avg_cpi', 10, 4)->nullable();
            $table->decimal('revenue_share_pct', 5, 2)->nullable();
            $table->unsignedInteger('payment_terms_days')->default(30);
            $table->string('currency', 3)->default('USD');
            // Quality & compliance
            $table->enum('brand_safety_tier', ['premium','standard','open'])->default('standard');
            $table->enum('fraud_detection_level', ['basic','advanced','premium'])->default('advanced');
            $table->boolean('gdpr_compliant')->default(true);
            $table->boolean('coppa_compliant')->default(false);
            // Status
            $table->enum('partnership_status', ['prospect','negotiating','active','paused','terminated'])->default('active')->index('idx_status');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('aq_oem_placements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->index('idx_manufacturer')->comment('FK → aq_oem_manufacturers');
            $table->enum('placement_type', ['setup_wizard','app_store','lockscreen','notification_tray','smart_folder','browser_default','search_recommendation','preinstall','firmware_update','content_widget','game_center','theme_store','weather_app','system_cleaner','file_manager'])->index('idx_placement_type');
            $table->string('placement_name', 255);
            $table->text('description')->nullable();
            // Placement specs
            $table->enum('ad_format', ['app_icon','banner','interstitial','native_card','notification','fullscreen','video','text_link','carousel'])->default('app_icon')->index('idx_format');
            $table->string('position', 100)->nullable();
            $table->enum('impression_type', ['view','click','install','engagement'])->default('view');
            $table->unsignedInteger('max_creatives')->default(1);
            $table->json('dimensions')->nullable();
            // Reach & performance
            $table->unsignedBigInteger('estimated_daily_impressions')->nullable();
            $table->decimal('avg_ctr', 8, 4)->nullable();
            $table->decimal('avg_conversion_rate', 8, 4)->nullable();
            // Targeting capabilities
            $table->boolean('supports_geo_targeting')->default(true);
            $table->boolean('supports_device_targeting')->default(true);
            $table->boolean('supports_demographic_targeting')->default(false);
            $table->boolean('supports_interest_targeting')->default(false);
            $table->boolean('supports_frequency_capping')->default(true);
            // Restrictions
            $table->string('min_os_version', 20)->nullable();
            $table->json('restricted_categories')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->unsignedInteger('approval_lead_time_days')->default(3);
            // Status
            $table->boolean('is_active')->default(true)->index('idx_active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('manufacturer_id', 'fk_op_manufacturer')->references('id')->on('aq_oem_manufacturers')->onDelete('cascade');
        });

        Schema::create('aq_oem_apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->index('idx_manufacturer')->comment('FK → aq_oem_manufacturers');
            $table->string('app_name', 255);
            $table->string('package_name', 255)->index('idx_package');
            $table->enum('app_category', ['browser','app_store','file_manager','weather','news','music','video','camera','gallery','cleaner','security','game_center','health','theme_store','keyboard','launcher','other'])->default('other')->index('idx_category');
            $table->enum('preinstall_type', ['system_app','partner_app','removable','non_removable','trial','optional_setup'])->default('partner_app')->index('idx_preinstall');
            // Reach
            $table->unsignedBigInteger('estimated_mau')->nullable();
            $table->unsignedBigInteger('estimated_dau')->nullable();
            $table->json('available_regions')->nullable();
            // Ad inventory
            $table->unsignedInteger('ad_placements_count')->default(0);
            $table->json('supported_ad_formats')->nullable();
            $table->unsignedInteger('avg_session_duration_s')->nullable();
            $table->decimal('avg_daily_sessions', 5, 2)->nullable();
            // Status
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('manufacturer_id', 'fk_oa_manufacturer')->references('id')->on('aq_oem_manufacturers')->onDelete('cascade');
        });

        Schema::create('aq_oem_device_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->index('idx_manufacturer')->comment('FK → aq_oem_manufacturers');
            $table->string('model_name', 255);
            $table->string('model_code', 100)->nullable();
            $table->string('series', 100)->nullable()->index('idx_series');
            $table->enum('tier', ['flagship','mid_range','budget','entry'])->default('mid_range')->index('idx_tier');
            $table->unsignedSmallInteger('release_year')->nullable()->index('idx_year');
            $table->string('os_version', 50)->nullable();
            $table->decimal('screen_size_inches', 4, 2)->nullable();
            $table->string('screen_resolution', 20)->nullable();
            $table->decimal('ram_gb', 4, 1)->nullable();
            $table->unsignedBigInteger('estimated_active_units')->nullable();
            // Ad capabilities
            $table->boolean('supports_lockscreen_ads')->default(false);
            $table->boolean('supports_setup_wizard_ads')->default(true);
            $table->boolean('supports_notification_ads')->default(true);
            $table->boolean('supports_preinstall')->default(true);
            // Targeting value
            $table->enum('avg_user_income_tier', ['low','medium','high','premium'])->nullable();
            $table->json('primary_markets')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('manufacturer_id', 'fk_odm_manufacturer')->references('id')->on('aq_oem_manufacturers')->onDelete('cascade');
        });

        Schema::create('aq_campaign_oem_assoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedBigInteger('manufacturer_id')->index('idx_manufacturer')->comment('FK → aq_oem_manufacturers');
            $table->unsignedBigInteger('placement_id')->nullable()->index('idx_placement')->comment('FK → aq_oem_placements');
            // Targeting refinements
            $table->json('device_tier_filter')->nullable();
            $table->json('device_model_ids')->nullable();
            $table->string('min_os_version', 20)->nullable();
            $table->json('geo_filter')->nullable();
            // Bid & budget
            $table->decimal('bid_adjustment_pct', 5, 2)->nullable();
            $table->decimal('daily_budget_limit', 10, 2)->nullable();
            $table->decimal('total_budget_limit', 10, 2)->nullable();
            // Creative
            $table->unsignedBigInteger('custom_creative_id')->nullable();
            $table->string('custom_app_name', 255)->nullable();
            $table->text('custom_description')->nullable();
            // Tracking
            $table->string('tracking_url', 2000)->nullable();
            $table->string('attribution_link', 2000)->nullable();
            // Status
            $table->enum('oem_approval_status', ['pending','approved','rejected','changes_requested'])->default('pending')->index('idx_approval');
            $table->dateTime('oem_approval_date')->nullable();
            $table->text('oem_rejection_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['campaign_id', 'campaign_source', 'manufacturer_id', 'placement_id'], 'uk_campaign_oem_placement');
            $table->foreign('manufacturer_id', 'fk_coa_manufacturer')->references('id')->on('aq_oem_manufacturers')->onDelete('cascade');
            $table->foreign('placement_id', 'fk_coa_placement')->references('id')->on('aq_oem_placements')->onDelete('set null');
        });

        Schema::create('aq_oem_performance_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index('idx_date');
            $table->unsignedBigInteger('campaign_id');
            $table->enum('campaign_source', ['rtb','direct'])->default('rtb');
            $table->unsignedBigInteger('manufacturer_id')->index('idx_manufacturer')->comment('FK → aq_oem_manufacturers');
            $table->unsignedBigInteger('placement_id')->nullable()->index('idx_placement');
            $table->unsignedBigInteger('device_model_id')->nullable()->index('idx_model');
            $table->char('country_code', 2)->nullable()->index('idx_country');
            // Volume metrics
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('installs')->default(0);
            $table->unsignedBigInteger('uninstalls')->default(0);
            $table->unsignedBigInteger('opens')->default(0);
            $table->unsignedBigInteger('registrations')->default(0);
            $table->unsignedBigInteger('purchases')->default(0);
            // Spend & revenue
            $table->decimal('spend', 12, 4)->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            // Rates
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('conversion_rate', 8, 4)->nullable();
            $table->decimal('cpi', 10, 4)->nullable();
            $table->decimal('cpa', 10, 4)->nullable();
            $table->decimal('roas', 8, 4)->nullable();
            $table->decimal('retention_d1_pct', 5, 2)->nullable();
            $table->decimal('retention_d7_pct', 5, 2)->nullable();
            $table->decimal('retention_d30_pct', 5, 2)->nullable();
            // Quality
            $table->unsignedBigInteger('fraud_blocked')->default(0);
            $table->decimal('fraud_rate_pct', 5, 2)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['date','campaign_id','campaign_source','manufacturer_id','placement_id','device_model_id','country_code'], 'uk_daily_oem');
            $table->index(['campaign_id', 'campaign_source'], 'idx_campaign');
            $table->foreign('manufacturer_id', 'fk_ops_manufacturer')->references('id')->on('aq_oem_manufacturers')->onDelete('cascade');
            $table->foreign('placement_id', 'fk_ops_placement')->references('id')->on('aq_oem_placements')->onDelete('set null');
            $table->foreign('device_model_id', 'fk_ops_model')->references('id')->on('aq_oem_device_models')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_oem_performance_stats');
        Schema::dropIfExists('aq_campaign_oem_assoc');
        Schema::dropIfExists('aq_oem_device_models');
        Schema::dropIfExists('aq_oem_apps');
        Schema::dropIfExists('aq_oem_placements');
        Schema::dropIfExists('aq_oem_manufacturers');
    }
};
