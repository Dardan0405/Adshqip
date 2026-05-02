<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class UserSubscription extends Model
{
    protected $table = 'aq_user_subscriptions';

    protected $fillable = [
        'user_id',
        'invoice_id',
        'plan_id',
        'billing_cycle',
        'payment_gateway',
        'gateway_txn_id',
        'gateway_subscription_id',
        'gateway_customer_id',
        'gateway_status',
        'gateway_response',
        'payment_reference',
        'auto_renew',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'next_renewal_at',
        'last_renewed_at',
        'renewal_attempts',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'next_renewal_at' => 'datetime',
        'last_renewed_at' => 'datetime',
        'auto_renew' => 'boolean',
        'renewal_attempts' => 'integer',
        'gateway_response' => 'array',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCurrent($query)
    {
        return $query->whereIn('status', ['active', 'trial']);
    }

    public function scopeDueForManualRenewal($query)
    {
        return $query
            ->where('auto_renew', true)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('gateway_status')
                    ->orWhere('gateway_status', '!=', 'pending_renewal_invoice');
            })
            ->whereNull('gateway_subscription_id')
            ->whereDate('current_period_end', '<=', now()->toDateString());
    }
}
