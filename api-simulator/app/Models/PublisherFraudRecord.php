<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherFraudRecord extends Model
{
    protected $table = 'aq_publisher_fraud_records';

    const UPDATED_AT = null;

    protected $fillable = [
        'publisher_id',
        'record_type',
        'reason',
        'flagged_impressions',
        'flagged_clicks',
        'action_taken',
        'created_at',
        'resolved_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'resolved_at' => 'datetime',
        'flagged_impressions' => 'integer',
        'flagged_clicks' => 'integer',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }
}
