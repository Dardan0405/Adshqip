<?php

namespace App\Models;

use App\Support\AdvertiserPaymentManager;
use Illuminate\Database\Eloquent\Model;

class AdvertiserDeposit extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(SavedPaymentMethod::class, 'payment_method_id');
    }

    public function scopeDeposits($query)
    {
        return $query->where('aq_transactions.type', 'deposit');
    }

    public function scopeForAdvertisers($query)
    {
        return $query
            ->join('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser')
            ->where('aq_users.is_deleted', false);
    }

    public function scopePaidBetween($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->whereDate('aq_transactions.completed_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('aq_transactions.completed_at', '<=', $endDate);
        }

        return $query;
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        $value = $this->payment_gateway ?: optional($this->paymentMethod)->type ?: PlatformSetting::getAdvertiserPaymentType();

        return app(AdvertiserPaymentManager::class)->paymentTypeLabel($value);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            'failed' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
            'reversed' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
        };
    }
}
