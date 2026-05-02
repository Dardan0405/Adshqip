<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $table = 'aq_payouts';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'payment_provider',
        'payment_reference',
        'gateway_reference',
        'gateway_response',
        'status',
        'period_start',
        'period_end',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'period_start' => 'date',
        'period_end' => 'date',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePending($query)
    {
        return $query->where('aq_payouts.status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('aq_payouts.status', 'completed');
    }

    public function scopePaidBetween($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->whereDate('aq_payouts.processed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('aq_payouts.processed_at', '<=', $endDate);
        }
        return $query;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if (filled($this->payment_provider)) {
            return match ($this->payment_provider) {
                'bankwire' => 'Bank Wire',
                'paypal' => 'PayPal',
                'bitcoin' => 'Bitcoin',
                'stripe' => 'Stripe',
                'authorize_net' => 'Authorize.net',
                default => ucfirst($this->payment_provider),
            };
        }

        return match ($this->payment_method) {
            'paypal' => 'PayPal',
            'wire_transfer' => 'Wire Transfer',
            'crypto' => 'Crypto',
            'payoneer' => 'Payoneer',
            default => ucfirst($this->payment_method ?? 'Unknown'),
        };
    }

    public function getPaymentProviderLabelAttribute(): string
    {
        if (! filled($this->payment_provider)) {
            return $this->payment_method_label;
        }

        return $this->payment_method_label;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
            'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            'failed' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
            'cancelled' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
        };
    }
}
