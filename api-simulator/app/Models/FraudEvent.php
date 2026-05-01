<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudEvent extends Model
{
    protected $table = 'aq_fraud_events';

    const UPDATED_AT = null;

    protected $fillable = [
        'event_type',
        'ad_id',
        'zone_id',
        'viewer_id',
        'fingerprint_id',
        'ip_address',
        'user_agent',
        'fraud_reason',
        'severity',
        'blocked',
        'created_at',
    ];

    protected $casts = [
        'blocked' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
