<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryWiseBidding extends Model
{
    protected $table = 'country_wise_bidding';

    protected $fillable = [
        'advertiser_id',
        'campaign_id',
        'campaign_type',
        'type',
        'country_code',
        'bid_value',
    ];

    protected $casts = [
        'bid_value' => 'decimal:2',
    ];

    /**
     * Get the advertiser associated with this bidding rule
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    /**
     * Get the campaign associated with this bidding rule (nullable)
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get the direct campaign associated with this bidding rule (nullable)
     */
    public function directCampaign(): BelongsTo
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }

    /**
     * Resolve the linked campaign across both campaign tables.
     */
    public function getSelectedCampaignAttribute(): Campaign|DirectCampaign|null
    {
        if (! $this->campaign_id) {
            return null;
        }

        if ($this->campaign_type === 'direct') {
            return $this->getRelationValue('directCampaign') ?? $this->directCampaign()->first();
        }

        return $this->getRelationValue('campaign') ?? $this->campaign()->first();
    }
}
