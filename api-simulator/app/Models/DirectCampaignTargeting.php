<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectCampaignTargeting extends Model
{
    use HasFactory;

    protected $table = 'aq_direct_campaign_targeting';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'campaign_id',
        'targeting_type',
        'match_mode',
        'target_values',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_values' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }
}
