<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdServingLog extends Model
{
    protected $table = 'aq_ad_serving_logs';

    protected $fillable = [
        'delivery_type',
        'event_type',
        'status',
        'campaign_id',
        'ad_id',
        'direct_campaign_id',
        'direct_creative_id',
        'zone_id',
        'site_id',
        'publisher_id',
        'advertiser_id',
        'request_id',
        'viewer_id',
        'click_id',
        'country_code',
        'device_type',
        'pricing_model',
        'bid_amount',
        'revenue',
        'publisher_earnings',
        'ip_address',
        'referer',
        'request_url',
        'destination_url',
        'user_agent',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'bid_amount' => 'decimal:4',
        'revenue' => 'decimal:4',
        'publisher_earnings' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function directCampaign()
    {
        return $this->belongsTo(DirectCampaign::class);
    }

    public function directCreative()
    {
        return $this->belongsTo(DirectCampaignCreative::class, 'direct_creative_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }
}
