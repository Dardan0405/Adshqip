<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aq_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id')->index('idx_advertiser');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('format_id')->nullable()->index('idx_format');
            $table->enum('marketing_objective', [
                'brand_awareness', 'reach', 'traffic', 'engagement', 'app_installs',
                'video_views', 'lead_generation', 'conversions', 'catalog_sales', 'store_visits'
            ])->default('traffic')->comment('Campaign marketing objective');
            $table->enum('campaign_type', ['cpm', 'cpc', 'cpa', 'cpv', 'cpv_ctw'])->default('cpm')->comment('Performance Marketing: CPA, CPC, CPM, CPV Click-to-Watch');
            $table->enum('status', ['draft', 'pending_review', 'active', 'paused', 'completed', 'rejected'])->default('draft')->index('idx_status');
            $table->tinyInteger('revenue_type')->default(1)->comment('old: revenue_type');
            $table->decimal('bid_amount', 10, 4)->default(0.0000)->comment('old: revenue');
            $table->decimal('daily_budget', 12, 4)->nullable()->comment('old: dj_daily_budget');
            $table->decimal('total_budget', 12, 4)->nullable()->comment('old: dj_campaign_budget');
            $table->decimal('remaining_budget', 12, 4)->nullable()->comment('old: dj_campaign_remain_budget');
            $table->string('currency', 3)->default('EUR');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('frequency_cap')->nullable()->comment('Max impressions per user per day');
            $table->enum('frequency_cap_period', ['hour', 'day', 'week', 'month', 'lifetime'])->default('day');
            $table->json('targeting_geo')->nullable()->comment('Country/region targeting, Balkans focus');
            $table->json('targeting_device')->nullable()->comment('desktop, mobile, tablet');
            $table->json('targeting_browser')->nullable();
            $table->json('targeting_os')->nullable();
            $table->json('targeting_language')->nullable();
            $table->json('targeting_schedule')->nullable()->comment('Day-parting schedule');
            $table->string('targeting_retargeting', 50)->nullable()->comment('old: dj_is_retargeted (MOBILE etc.)');
            $table->text('blocked_domains')->nullable()->comment('old: bdomain_value');
            $table->string('blocked_categories', 500)->nullable()->comment('old: bcat_value');
            // Distribution network & MSN exclusive
            $table->enum('distribution_mode', ['all_networks', 'selected_networks', 'msn_exclusive'])->default('all_networks')->comment('Where ads are delivered');
            $table->boolean('msn_exclusive')->default(false)->comment('Quick toggle: Run on MSN exclusively');
            $table->boolean('msn_enabled')->default(false)->comment('Include MSN network in distribution');
            $table->decimal('msn_bid_adjustment', 5, 2)->nullable()->comment('Bid modifier % for MSN placements');
            // Campaign Dynamics
            $table->boolean('dynamic_creative_enabled')->default(false)->comment('Enable Dynamic Creative Optimization');
            $table->boolean('dynamic_tokens_enabled')->default(false)->comment('Enable dynamic token replacement');
            $table->unsignedBigInteger('dynamic_product_feed_id')->nullable()->comment('FK → aq_dynamic_product_feeds');
            $table->boolean('dynamic_landing_page_enabled')->default(false)->comment('Enable per-segment dynamic landing page URL rules');
            $table->boolean('dynamic_budget_rules_enabled')->default(false)->comment('Enable automated budget/bid rules');
            // Custom Audience targeting
            $table->enum('audience_targeting_mode', ['none', 'include', 'exclude', 'both'])->default('none')->comment('How custom audiences are applied');
            $table->boolean('audience_expansion_enabled')->default(false)->comment('Allow platform to expand reach via lookalike modeling');
            $table->decimal('audience_expansion_ratio', 3, 2)->default(1.00)->comment('Lookalike expansion ratio 1-10');
            // OEM targeting
            $table->boolean('oem_enabled')->default(false)->comment('Enable OEM distribution');
            $table->enum('oem_targeting_mode', ['none', 'all_oems', 'selected_oems'])->default('none')->comment('Target all OEM partners or select specific manufacturers');
            $table->decimal('oem_bid_adjustment', 5, 2)->nullable()->comment('Bid modifier % for OEM placements');
            $table->json('oem_placement_types')->nullable()->comment('Preferred OEM placements');
            $table->integer('weight')->default(1)->comment('old: campaign_weight');
            $table->boolean('admin_approved')->default(false)->comment('old: dj_admin_approve');
            $table->boolean('is_deleted')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['start_date', 'end_date'], 'idx_dates');
            $table->foreign('advertiser_id', 'fk_campaign_advertiser')->references('id')->on('aq_users')->onDelete('cascade');
            $table->foreign('format_id', 'fk_campaign_format')->references('id')->on('aq_ad_formats')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aq_campaigns');
    }
};
