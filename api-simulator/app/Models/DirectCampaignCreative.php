<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectCampaignCreative extends Model
{
    use HasFactory;

    protected $table = 'aq_direct_campaign_creatives';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'campaign_id',
        'variant_label',
        'creative_type',
        'file_path',
        'file_type',
        'mime_type',
        'file_size_bytes',
        'width',
        'height',
        'duration_seconds',
        'alt_text',
        'headline',
        'body_text',
        'call_to_action',
        'destination_url',
        'is_primary',
        'is_winner',
        'status',
        'admin_approved',
        'impressions',
        'clicks',
        'conversions',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_winner' => 'boolean',
            'admin_approved' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(DirectCampaign::class, 'campaign_id');
    }
}
