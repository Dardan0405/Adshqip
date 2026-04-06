<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectCampaignStat extends Model
{
    use HasFactory;

    protected $table = 'aq_direct_campaign_stats';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'date',
        'campaign_id',
        'creative_id',
        'zone_id',
        'country_code',
        'device_type',
        'impressions',
        'viewable_impressions',
        'adblock_detected',
        'clicks',
        'unique_clicks',
        'conversions',
        'revenue',
        'publisher_earnings',
        'ecpm',
        'ctr',
        'conversion_rate',
        'fill_rate',
        'avg_cpc',
        'avg_cpa',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }

    public function creative()
    {
        return $this->belongsTo(DirectCampaignCreative::class, 'creative_id');
    }
}
