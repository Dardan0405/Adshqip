<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlAdReport extends Model
{
    protected $table = 'aq_url_ad_reports';

    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'campaign_id',
        'direct_campaign_id',
        'direct_creative_id',
        'zone_id',
        'event_type',
        'request_url',
        'referrer_url',
        'tracking_url',
        'destination_url',
        'device_type',
        'ip_address',
        'user_agent',
        'url_hidden',
        'url_encoded',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'url_hidden' => 'boolean',
            'url_encoded' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
