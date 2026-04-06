<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectCampaign extends Model
{
    use HasFactory;

    protected $table = 'aq_direct_campaigns';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'advertiser_id',
        'name',
        'description',
        'format_id',
        'marketing_objective',
        'pricing_model',
        'bid_amount',
        'daily_budget',
        'total_budget',
        'remaining_budget',
        'currency',
        'start_date',
        'end_date',
        'schedule_timezone',
        'dayparting',
        'frequency_cap',
        'frequency_cap_period',
        'delivery_mode',
        'priority',
        'weight',
        'destination_url',
        'display_url',
        'tracking_url',
        'click_tracking_url',
        'headline',
        'headline_dki',
        'headline_dki_default',
        'body_text',
        'body_text_dki',
        'call_to_action',
        'sponsored_label',
        'brand_name',
        'brand_logo_url',
        'brand_tagline',
        'brand_color_primary',
        'brand_color_secondary',
        'ctw_enabled',
        'ctw_thumbnail_url',
        'ctw_min_watch_seconds',
        'ctw_skip_after_seconds',
        'ctw_autoplay',
        'ctw_muted_autoplay',
        'video_brand_logo_url',
        'video_brand_logo_position',
        'video_brand_logo_opacity',
        'video_brand_intro_url',
        'video_brand_intro_duration',
        'end_card_enabled',
        'end_card_type',
        'end_card_image_url',
        'end_card_html',
        'end_card_headline',
        'end_card_body',
        'end_card_cta_text',
        'end_card_cta_url',
        'end_card_cta_color',
        'end_card_display_seconds',
        'end_card_logo_url',
        'clip_enabled',
        'clip_video_url',
        'clip_thumbnail_url',
        'clip_duration_seconds',
        'clip_aspect_ratio',
        'clip_sound_default',
        'clip_autoplay',
        'clip_loop',
        'clip_swipe_up_enabled',
        'clip_swipe_up_text',
        'clip_swipe_up_url',
        'clip_caption',
        'clip_hashtags',
        'clip_music_track_id',
        'clip_sticker_overlays',
        'clip_text_overlays',
        'clip_interactive_poll',
        'clip_shoppable_products',
        'adshqipai_type',
        'adshqipai_prompt',
        'adshqipai_style',
        'adshqipai_motion_template',
        'adshqipai_generated_asset_url',
        'adshqipai_generation_id',
        'adshqipai_model_version',
        'adshqipai_is_edited',
        'estimated_daily_impressions',
        'estimated_daily_clicks',
        'estimated_reach',
        'ab_test_enabled',
        'ab_test_split_percent',
        'ab_winner_metric',
        'ab_auto_optimize',
        'inline_optimization',
        'inline_optimization_mode',
        'spendguard_enabled',
        'spendguard_buffer_pct',
        'perf_stimulator_enabled',
        'perf_stimulator_target_metric',
        'perf_stimulator_boost_pct',
        'pacing_health_score',
        'pacing_health_status',
        'distribution_mode',
        'msn_exclusive',
        'msn_enabled',
        'msn_bid_adjustment',
        'dynamic_creative_enabled',
        'dynamic_tokens_enabled',
        'dynamic_product_feed_id',
        'dynamic_landing_page_enabled',
        'dynamic_budget_rules_enabled',
        'audience_targeting_mode',
        'audience_expansion_enabled',
        'audience_expansion_ratio',
        'oem_enabled',
        'oem_targeting_mode',
        'oem_bid_adjustment',
        'oem_placement_types',
        'status',
        'admin_approved',
        'rejection_reason',
        'parent_campaign_id',
        'campaign_group_name',
        'notes',
        'tags',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'dayparting' => 'array',
            'clip_hashtags' => 'array',
            'clip_sticker_overlays' => 'array',
            'clip_text_overlays' => 'array',
            'clip_interactive_poll' => 'array',
            'clip_shoppable_products' => 'array',
            'oem_placement_types' => 'array',
            'tags' => 'array',
            'ctw_enabled' => 'boolean',
            'ctw_autoplay' => 'boolean',
            'ctw_muted_autoplay' => 'boolean',
            'end_card_enabled' => 'boolean',
            'clip_enabled' => 'boolean',
            'clip_autoplay' => 'boolean',
            'clip_loop' => 'boolean',
            'clip_swipe_up_enabled' => 'boolean',
            'adshqipai_is_edited' => 'boolean',
            'ab_test_enabled' => 'boolean',
            'ab_auto_optimize' => 'boolean',
            'inline_optimization' => 'boolean',
            'spendguard_enabled' => 'boolean',
            'perf_stimulator_enabled' => 'boolean',
            'msn_exclusive' => 'boolean',
            'msn_enabled' => 'boolean',
            'dynamic_creative_enabled' => 'boolean',
            'dynamic_tokens_enabled' => 'boolean',
            'dynamic_landing_page_enabled' => 'boolean',
            'dynamic_budget_rules_enabled' => 'boolean',
            'audience_expansion_enabled' => 'boolean',
            'oem_enabled' => 'boolean',
            'admin_approved' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function creatives()
    {
        return $this->hasMany(DirectCampaignCreative::class, 'campaign_id');
    }

    public function targeting()
    {
        return $this->hasMany(DirectCampaignTargeting::class, 'campaign_id');
    }

    public function zones()
    {
        return $this->hasMany(DirectCampaignZone::class, 'campaign_id');
    }

    public function activeZoneLink()
    {
        return $this->hasOne(DirectCampaignZone::class, 'campaign_id')
            ->where('is_active', true)
            ->latestOfMany('id');
    }

    public function stats()
    {
        return $this->hasMany(DirectCampaignStat::class, 'campaign_id');
    }

    public function parentCampaign()
    {
        return $this->belongsTo(DirectCampaign::class, 'parent_campaign_id');
    }
}
