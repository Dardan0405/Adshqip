<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_direct_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser')->comment('FK → aq_users (role=advertiser)');
            $table->string('name', 255)->comment('AdGate: campaign name in builder');
            $table->text('description')->nullable();
            $table->unsignedInteger('format_id')->nullable()->index('idx_format')->comment('FK → aq_ad_formats');
            $table->enum('marketing_objective', [
                'brand_awareness','reach','traffic','engagement','app_installs',
                'video_views','lead_generation','conversions','catalog_sales','store_visits'
            ])->default('traffic');
            // Pricing model
            $table->enum('pricing_model', ['cpm','cpc','cpa','cpv','cpv_ctw','flat_rate'])->default('cpm')->index('idx_pricing');
            $table->decimal('bid_amount', 10, 4)->default(0.0000);
            $table->decimal('daily_budget', 12, 4)->nullable();
            $table->decimal('total_budget', 12, 4)->nullable();
            $table->decimal('remaining_budget', 12, 4)->nullable();
            $table->string('currency', 3)->default('EUR');
            // Scheduling
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('schedule_timezone', 50)->default('Europe/Tirane');
            $table->json('dayparting')->nullable()->comment('Hour-of-day / day-of-week schedule grid');
            // Frequency & delivery
            $table->integer('frequency_cap')->nullable();
            $table->enum('frequency_cap_period', ['hour','day','week','month','lifetime'])->default('day');
            $table->enum('delivery_mode', ['standard','accelerated'])->default('standard')->comment('Pacing strategy');
            $table->integer('priority')->default(5);
            $table->integer('weight')->default(1);
            // Destination
            $table->text('destination_url')->comment('Click-through URL');
            $table->text('display_url')->nullable();
            $table->text('tracking_url')->nullable();
            $table->text('click_tracking_url')->nullable();
            // Ad content
            $table->string('headline', 255)->nullable();
            $table->string('headline_dki', 255)->nullable();
            $table->string('headline_dki_default', 255)->nullable();
            $table->text('body_text')->nullable();
            $table->text('body_text_dki')->nullable();
            $table->string('call_to_action', 50)->nullable();
            $table->string('sponsored_label', 50)->default('sponsored');
            // Campaign branding
            $table->string('brand_name', 100)->nullable();
            $table->text('brand_logo_url')->nullable();
            $table->string('brand_tagline', 255)->nullable();
            $table->char('brand_color_primary', 7)->nullable();
            $table->char('brand_color_secondary', 7)->nullable();
            // CTW video settings
            $table->boolean('ctw_enabled')->default(false);
            $table->text('ctw_thumbnail_url')->nullable();
            $table->unsignedInteger('ctw_min_watch_seconds')->default(5);
            $table->unsignedInteger('ctw_skip_after_seconds')->nullable();
            $table->boolean('ctw_autoplay')->default(false);
            $table->boolean('ctw_muted_autoplay')->default(true);
            // Video branding overlay
            $table->text('video_brand_logo_url')->nullable();
            $table->enum('video_brand_logo_position', ['top_left','top_right','bottom_left','bottom_right'])->default('bottom_right');
            $table->decimal('video_brand_logo_opacity', 3, 2)->default(0.80);
            $table->text('video_brand_intro_url')->nullable();
            $table->unsignedInteger('video_brand_intro_duration')->nullable();
            // End Card settings
            $table->boolean('end_card_enabled')->default(false);
            $table->enum('end_card_type', ['static_image','html','cta_button','product_feed','custom'])->default('cta_button');
            $table->text('end_card_image_url')->nullable();
            $table->text('end_card_html')->nullable();
            $table->string('end_card_headline', 255)->nullable();
            $table->text('end_card_body')->nullable();
            $table->string('end_card_cta_text', 50)->nullable();
            $table->text('end_card_cta_url')->nullable();
            $table->char('end_card_cta_color', 7)->nullable();
            $table->unsignedInteger('end_card_display_seconds')->default(10);
            $table->text('end_card_logo_url')->nullable();
            // Clip Ad settings
            $table->boolean('clip_enabled')->default(false);
            $table->text('clip_video_url')->nullable();
            $table->text('clip_thumbnail_url')->nullable();
            $table->unsignedInteger('clip_duration_seconds')->nullable();
            $table->enum('clip_aspect_ratio', ['9:16','4:5','1:1'])->default('9:16');
            $table->enum('clip_sound_default', ['on','off'])->default('on');
            $table->boolean('clip_autoplay')->default(true);
            $table->boolean('clip_loop')->default(true);
            $table->boolean('clip_swipe_up_enabled')->default(true);
            $table->string('clip_swipe_up_text', 100)->default('Shiko Më Shumë');
            $table->text('clip_swipe_up_url')->nullable();
            $table->text('clip_caption')->nullable();
            $table->json('clip_hashtags')->nullable();
            $table->unsignedBigInteger('clip_music_track_id')->nullable();
            $table->json('clip_sticker_overlays')->nullable();
            $table->json('clip_text_overlays')->nullable();
            $table->json('clip_interactive_poll')->nullable();
            $table->json('clip_shoppable_products')->nullable();
            // adshqipAI
            $table->enum('adshqipai_type', ['none','ad_maker','motion','motion_prompt'])->default('none');
            $table->text('adshqipai_prompt')->nullable();
            $table->string('adshqipai_style', 100)->nullable();
            $table->string('adshqipai_motion_template', 100)->nullable();
            $table->text('adshqipai_generated_asset_url')->nullable();
            $table->string('adshqipai_generation_id', 128)->nullable();
            $table->string('adshqipai_model_version', 50)->nullable();
            $table->boolean('adshqipai_is_edited')->default(false);
            // Traffic estimator
            $table->unsignedBigInteger('estimated_daily_impressions')->nullable();
            $table->unsignedBigInteger('estimated_daily_clicks')->nullable();
            $table->unsignedBigInteger('estimated_reach')->nullable();
            // A/B testing
            $table->boolean('ab_test_enabled')->default(false);
            $table->unsignedTinyInteger('ab_test_split_percent')->default(50);
            $table->enum('ab_winner_metric', ['ctr','conversions','ecpm','viewability'])->default('ctr');
            $table->boolean('ab_auto_optimize')->default(false);
            // Optimization Tools
            $table->boolean('inline_optimization')->default(false);
            $table->enum('inline_optimization_mode', ['conservative','balanced','aggressive'])->default('balanced');
            $table->boolean('spendguard_enabled')->default(false);
            $table->decimal('spendguard_buffer_pct', 5, 2)->default(5.00);
            $table->boolean('perf_stimulator_enabled')->default(false);
            $table->enum('perf_stimulator_target_metric', ['ctr','conversions','roas','ecpm'])->default('conversions');
            $table->decimal('perf_stimulator_boost_pct', 5, 2)->default(20.00);
            $table->decimal('pacing_health_score', 5, 2)->nullable();
            $table->enum('pacing_health_status', ['healthy','under_pacing','over_pacing','critical'])->nullable();
            // Distribution network & MSN
            $table->enum('distribution_mode', ['all_networks','selected_networks','msn_exclusive'])->default('all_networks');
            $table->boolean('msn_exclusive')->default(false);
            $table->boolean('msn_enabled')->default(false);
            $table->decimal('msn_bid_adjustment', 5, 2)->nullable();
            // Campaign Dynamics
            $table->boolean('dynamic_creative_enabled')->default(false);
            $table->boolean('dynamic_tokens_enabled')->default(false);
            $table->unsignedBigInteger('dynamic_product_feed_id')->nullable();
            $table->boolean('dynamic_landing_page_enabled')->default(false);
            $table->boolean('dynamic_budget_rules_enabled')->default(false);
            // Custom Audience targeting
            $table->enum('audience_targeting_mode', ['none','include','exclude','both'])->default('none');
            $table->boolean('audience_expansion_enabled')->default(false);
            $table->decimal('audience_expansion_ratio', 3, 2)->default(1.00);
            // OEM targeting
            $table->boolean('oem_enabled')->default(false);
            $table->enum('oem_targeting_mode', ['none','all_oems','selected_oems'])->default('none');
            $table->decimal('oem_bid_adjustment', 5, 2)->nullable();
            $table->json('oem_placement_types')->nullable();
            // Status & approval
            $table->enum('status', ['draft','pending_review','active','paused','completed','rejected','archived'])->default('draft')->index('idx_status');
            $table->boolean('admin_approved')->default(false);
            $table->text('rejection_reason')->nullable();
            // Bulk campaign support
            $table->unsignedBigInteger('parent_campaign_id')->nullable()->index('idx_parent');
            $table->string('campaign_group_name', 255)->nullable();
            // Metadata
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['start_date', 'end_date'], 'idx_dates');
            $table->foreign('advertiser_id', 'fk_dc_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('format_id', 'fk_dc_format')->references('id')->on('aq_ad_formats')->onDelete('set null');
            $table->foreign('parent_campaign_id', 'fk_dc_parent')->references('id')->on('aq_direct_campaigns')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_direct_campaigns');
    }
};
