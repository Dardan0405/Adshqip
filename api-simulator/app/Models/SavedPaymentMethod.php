<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPaymentMethod extends Model
{
    protected $table = 'aq_saved_payment_methods';

    protected $fillable = [
        'user_id',
        'label',
        'type',
        'gateway',
        'gateway_customer_id',
        'gateway_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'billing_country',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
