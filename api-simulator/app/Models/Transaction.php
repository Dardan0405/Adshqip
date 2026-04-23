<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(SavedPaymentMethod::class, 'payment_method_id');
    }

    public function scopeDeposits($query)
    {
        return $query->where('aq_transactions.type', 'deposit');
    }

    public function scopeAdSpend($query)
    {
        return $query->where('aq_transactions.type', 'ad_spend');
    }

    public function scopeCompleted($query)
    {
        return $query->where('aq_transactions.status', 'completed');
    }

    public function scopeCompletedAdvertiserDeposits($query)
    {
        return $query->deposits()
            ->completed()
            ->join('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id')
            ->where('aq_users.role', 'advertiser');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('aq_transactions.type', 'withdrawal');
    }

    public function scopeRefunds($query)
    {
        return $query->where('aq_transactions.type', 'refund');
    }

    public function scopeBetweenDates($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->whereDate('aq_transactions.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('aq_transactions.created_at', '<=', $endDate);
        }

        return $query;
    }

    public function getFormattedAmountAttribute(): string
    {
        $symbol = match ($this->currency) {
            'EUR' => 'EUR ',
            'USD' => 'USD ',
            'GBP' => 'GBP ',
            default => ((string) $this->currency) . ' ',
        };

        return $symbol . number_format((float) $this->amount, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return Str::headline(str_replace('_', ' ', (string) $this->type));
    }

    public function getGatewayLabelAttribute(): string
    {
        $value = $this->payment_gateway ?: optional($this->paymentMethod)->gateway;

        if (! $value) {
            return 'N/A';
        }

        return match ($value) {
            'wire_transfer' => 'Bank Wire',
            'coinbase', 'bitcoin' => 'Bitcoin',
            default => Str::headline((string) $value),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
            'failed' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
            'reversed' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
        };
    }
}
