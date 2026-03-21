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
        'targeting_os',
        'targeting_language',
        'targeting_schedule',
        'targeting_region',
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
            'targeting_os' => 'array',
            'targeting_language' => 'array',
            'targeting_schedule' => 'array',
            'targeting_region' => 'array',
            'traffic_sources' => 'array',
            'country_bids' => 'array',
            'ad_formats' => 'array',
            'oem_placement_types' => 'array',
            'admin_approved' => 'boolean',
            'is_deleted' => 'boolean',
            'dynamic_creative_enabled' => 'boolean',
            'msn_exclusive' => 'boolean',
            'msn_enabled' => 'boolean',
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

    // Tracking relationships (to be implemented later)
    // public function impressions()
    // {
    //     return $this->hasMany(Impression::class, 'campaign_id');
    // }

    // public function clicks()
    // {
    //     return $this->hasMany(Click::class, 'campaign_id');
    // }

    // public function conversions()
    // {
    //     return $this->hasMany(Conversion::class, 'campaign_id');
    // }
}
