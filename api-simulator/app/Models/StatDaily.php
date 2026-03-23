<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatDaily extends Model
{
    protected $table = 'aq_stats_daily';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'ad_id',
        'campaign_id',
        'zone_id',
        'site_id',
        'advertiser_id',
        'publisher_id',
        'format_id',
        'country_code',
        'device_type',
        'impressions',
        'unique_impressions',
        'clicks',
        'unique_clicks',
        'conversions',
        'viewable_impressions',
        'adblock_detected',
        'revenue',
        'publisher_earnings',
        'ecpm',
        'ctr',
        'fill_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
