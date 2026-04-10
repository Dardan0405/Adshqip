<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPayout extends Model
{
    use HasFactory;

    protected $table = 'aq_referral_payouts';

    protected $fillable = [
        'referrer_id',
        'amount',
        'currency',
        'payment_method',
        'payment_reference',
        'period_start',
        'period_end',
        'conversions_count',
        'status',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'period_start' => 'date',
        'period_end' => 'date',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the referrer user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Scope for completed payouts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get invoice number generated from ID.
     */
    public function getInvoiceNumberAttribute()
    {
        return 'REF-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        return $symbol . number_format($this->amount, 2);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
            'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            'failed' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
            'cancelled' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
        };
    }
}
