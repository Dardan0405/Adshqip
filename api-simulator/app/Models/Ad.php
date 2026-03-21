<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $table = 'aq_ads';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'campaign_id',
        'name',
        'ad_type',
        'status',
        'destination_url',
        'display_url',
        'headline',
        'body_text',
        'call_to_action',
        'sponsored_label',
        'brand_name',
        'brand_logo_url',
        'admin_approved',
        'weight',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'admin_approved' => 'boolean',
            'is_deleted' => 'boolean',
            'ctw_enabled' => 'boolean',
            'end_card_enabled' => 'boolean',
            'clip_enabled' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function creatives()
    {
        return $this->hasMany(AdCreative::class, 'ad_id');
    }

    public function primaryCreative()
    {
        return $this->hasOne(AdCreative::class, 'ad_id')->where('is_primary', true);
    }
}
