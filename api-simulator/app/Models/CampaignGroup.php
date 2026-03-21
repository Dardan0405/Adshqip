<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignGroup extends Model
{
    use HasFactory;

    protected $table = 'aq_campaign_groups';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'description',
        'advertiser_id',
        'status',
        'campaign_count',
        'total_budget',
        'color',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
            'campaign_count' => 'integer',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'group_id');
    }
}
