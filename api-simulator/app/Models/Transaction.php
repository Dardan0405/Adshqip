<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'aq_transactions';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'payment_method_id',
        'payment_gateway',
        'gateway_txn_id',
        'gateway_status',
        'gateway_response',
        'campaign_id',
        'invoice_id',
        'description',
        'admin_note',
        'initiated_by',
        'status',
        'completed_at',
        'ip_address',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'gateway_response' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign associated with the transaction (for ad_spend type).
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Scope a query to only include deposit transactions.
     */
    public function scopeDeposits($query)
    {
        return $query->where('aq_transactions.type', 'deposit');
    }

    /**
     * Scope a query to only include ad spend transactions.
     */
    public function scopeAdSpend($query)
    {
        return $query->where('aq_transactions.type', 'ad_spend');
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('aq_transactions.status', 'completed');
    }

    /**
     * Scope a query to completed advertiser deposit transactions.
     */
    public function scopeCompletedAdvertiserDeposits($query)
    {
        return $query->deposits()
            ->completed()
            ->join('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser');
    }

    /**
     * Scope a query to only include withdrawals.
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('aq_transactions.type', 'withdrawal');
    }

    /**
     * Scope a query to only include refunds.
     */
    public function scopeRefunds($query)
    {
        return $query->where('aq_transactions.type', 'refund');
    }

    /**
     * Get formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        return $symbol . number_format($this->amount, 2);
    }
}
