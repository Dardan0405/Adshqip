<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneLimitation extends Model
{
    protected $table = 'zone_limitations';

    protected $fillable = [
        'advertiser_id',
        'name',
        'type',
        'zone_ids',
    ];

    protected $casts = [
        'zone_ids' => 'array',
    ];

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function getZoneNamesAttribute()
    {
        if (empty($this->zone_ids)) {
            return collect();
        }

        return Zone::whereIn('id', $this->zone_ids)->pluck('name', 'id');
    }
}
