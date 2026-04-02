<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectCampaignZone extends Model
{
    use HasFactory;

    protected $table = 'aq_direct_campaign_zones';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'campaign_id',
        'zone_id',
        'priority',
        'floor_price_override',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }
}
