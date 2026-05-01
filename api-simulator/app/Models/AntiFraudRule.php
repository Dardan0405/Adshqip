<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntiFraudRule extends Model
{
    protected $table = 'aq_antifraud_rules';

    protected $fillable = [
        'rule_name',
        'rule_type',
        'match_value',
        'threshold_value',
        'reset_period_seconds',
        'action',
        'severity',
        'description',
        'is_active',
        'last_triggered_at',
        'triggered_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'triggered_count' => 'integer',
        'threshold_value' => 'integer',
        'reset_period_seconds' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function markTriggered(): void
    {
        $this->forceFill(['last_triggered_at' => now()])->save();
        $this->increment('triggered_count');
    }
}
