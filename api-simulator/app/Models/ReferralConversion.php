<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralConversion extends Model
{
    protected $table = 'aq_referral_conversions';

    protected $fillable = [
        'link_id',
        'referrer_id',
        'referred_user_id',
        'referred_role',
        'click_ip',
        'click_user_agent',
        'click_referer',
        'signup_ip',
        'cookie_id',
        'is_qualified',
        'qualified_at',
        'qualification_threshold',
        'commission_earned',
        'commission_currency',
        'commission_ends_at',
        'status',
        'fraud_flags',
    ];

    protected function casts(): array
    {
        return [
            'is_qualified' => 'boolean',
            'qualified_at' => 'datetime',
            'qualification_threshold' => 'decimal:4',
            'commission_earned' => 'decimal:4',
            'commission_ends_at' => 'datetime',
            'fraud_flags' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function link()
    {
        return $this->belongsTo(ReferralLink::class, 'link_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
