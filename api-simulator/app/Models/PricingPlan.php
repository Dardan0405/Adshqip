<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $table = 'aq_pricing_plans';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'target_audience',
        'price_monthly',
        'price_yearly',
        'currency',
        'features',
        'impressions_limit',
        'is_popular',
        'is_enterprise',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'impressions_limit' => 'integer',
        'is_popular' => 'boolean',
        'is_enterprise' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
