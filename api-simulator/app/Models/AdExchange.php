<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdExchange extends Model
{
    protected $table = 'aq_ad_exchanges';

    protected $fillable = [
        'name',
        'type',
        'endpoint_url',
        'auth_type',
        'credentials',
        'seller_id',
        'auction_currency',
        'auction_type',
        'is_strict_openrtb',
        'status',
        'agency_id',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_strict_openrtb' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'DSP',
        'auth_type' => 'api_key',
        'auction_currency' => 'EUR',
        'auction_type' => 2,
        'is_strict_openrtb' => true,
        'status' => 'active',
    ];

    /**
     * Get the agency that owns the ad exchange
     */
    public function agency()
    {
        return $this->belongsTo(User::class, 'agency_id');
    }
}
