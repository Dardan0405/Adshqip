<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = 'aq_campaigns';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'advertiser_id',
        'group_id',
        'zone_id',
        'name',
        'description',
        'format_id',
        'pixel_tracker_id',
        'marketing_objective',
        'campaign_type',
        'status',
        'bid_amount',
        'daily_budget',
        'total_budget',
        'remaining_budget',
        'currency',
        'start_date',
        'end_date',
        'frequency_cap',
        'frequency_cap_period',
        'targeting_geo',
        'targeting_device',
        'targeting_browser',
        'targeting_browser_version',
        'targeting_os',
        'targeting_os_version',
        'targeting_language',
        'targeting_schedule',
        'targeting_region',
        'targeting_city',
        'targeting_connection_type',
        'targeting_carrier',
        'targeting_traffic_type',
        'targeting_ip_include',
        'targeting_ip_exclude',
        'targeting_keywords',
        's2s_postback_url',
        's2s_enabled',
        'traffic_sources',
        'country_bids',
        'ad_formats',
        'targeting_retargeting',
        'blocked_domains',
        'blocked_categories',
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
        'admarket_enabled',
        'admarket_status',
        'admarket_notes',
        'admarket_published_at',
        'weight',
        'admin_approved',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'targeting_geo' => 'array',
            'targeting_device' => 'array',
            'targeting_browser' => 'array',
            'targeting_browser_version' => 'array',
            'targeting_os' => 'array',
            'targeting_os_version' => 'array',
            'targeting_language' => 'array',
            'targeting_schedule' => 'array',
            'targeting_region' => 'array',
            'targeting_city' => 'array',
            'targeting_connection_type' => 'array',
            'targeting_carrier' => 'array',
            'targeting_ip_include' => 'array',
            'targeting_ip_exclude' => 'array',
            'targeting_keywords' => 'array',
            'traffic_sources' => 'array',
            'country_bids' => 'array',
            'ad_formats' => 'array',
            'oem_placement_types' => 'array',
            's2s_enabled' => 'boolean',
            'admin_approved' => 'boolean',
            'is_deleted' => 'boolean',
            'dynamic_creative_enabled' => 'boolean',
            'msn_exclusive' => 'boolean',
            'msn_enabled' => 'boolean',
            'admarket_enabled' => 'boolean',
            'admarket_published_at' => 'datetime',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function group()
    {
        return $this->belongsTo(CampaignGroup::class, 'group_id');
    }

    public function pixelTracker()
    {
        return $this->belongsTo(PixelTracker::class, 'pixel_tracker_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'campaign_id');
    }

    public function stats()
    {
        return $this->hasMany(StatDaily::class, 'campaign_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'aq_campaign_category', 'campaign_id', 'category_id');
    }

    public function audiences()
    {
        return $this->belongsToMany(AdvertiserAudience::class, 'aq_campaign_audience', 'campaign_id', 'audience_id')
            ->withPivot('mode')
            ->withTimestamps();
    }
}
