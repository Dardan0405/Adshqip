<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $table = 'aq_referral_links';

    protected $fillable = [
        'referrer_id',
        'code',
        'slug',
        'target_role',
        'campaign_name',
        'landing_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'commission_type',
        'commission_rate',
        'commission_duration_days',
        'max_commission_per_referral',
        'total_clicks',
        'total_signups',
        'total_qualified',
        'total_earned',
        'status',
        'expires_at',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'max_commission_per_referral' => 'decimal:4',
            'total_earned' => 'decimal:4',
            'expires_at' => 'datetime',
            'is_deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function conversions()
    {
        return $this->hasMany(ReferralConversion::class, 'link_id');
    }
}
