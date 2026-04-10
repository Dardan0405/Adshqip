<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'aq_invoices';

    // Disable updated_at timestamp since the table doesn't have it
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'type',
        'amount',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'pdf_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user (publisher/advertiser) that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include publisher invoices.
     */
    public function scopePublisherInvoices($query)
    {
        return $query->where('type', 'publisher_payout');
    }

    /**
     * Scope a query to only include advertiser invoices.
     */
    public function scopeAdvertiserInvoices($query)
    {
        return $query->where('type', 'advertiser_charge');
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending/unpaid invoices.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Get formatted total amount with currency symbol.
     */
    public function getFormattedTotalAttribute()
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        return $symbol . number_format($this->total_amount, 2);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
            'sent' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
            'overdue' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
            'cancelled' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
            default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
        };
    }
}
