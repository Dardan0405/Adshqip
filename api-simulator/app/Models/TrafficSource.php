<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficSource extends Model
{
    protected $table = 'aq_traffic_source_bidding';

    protected $fillable = [
        'traffic_source_id',
        'bid_rate',
        'status',
        'campaign_id',
        'campaign_type',
    ];

    protected $casts = [
        'bid_rate' => 'decimal:2',
    ];

    /**
     * Get the source lookup record.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(TrafficSourceLookup::class, 'traffic_source_id');
    }

    /**
     * Get the campaign associated with this traffic source (network).
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get the direct campaign associated with this traffic source.
     */
    public function directCampaign(): BelongsTo
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }
}
