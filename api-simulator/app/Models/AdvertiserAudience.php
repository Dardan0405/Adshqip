<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserAudience extends Model
{
    protected $table = 'aq_advertiser_audiences';

    protected $fillable = [
        'advertiser_id',
        'name',
        'slug',
        'type',
        'status',
        'source',
        'description',
        'countries',
        'devices',
        'interests',
        'keywords',
        'rules',
        'estimated_size',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'devices' => 'array',
            'interests' => 'array',
            'keywords' => 'array',
            'rules' => 'array',
            'estimated_size' => 'integer',
            'is_deleted' => 'boolean',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'aq_campaign_audience', 'audience_id', 'campaign_id')
            ->withPivot('mode')
            ->withTimestamps();
    }
}
