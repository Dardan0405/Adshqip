<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdFormat extends Model
{
    use HasFactory;

    protected $table = 'aq_ad_formats';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'ecpm_avg',
        'fill_rate_avg',
        'ctr_avg',
        'supports_mobile',
        'supports_desktop',
        'supports_amp',
        'gdpr_ready',
        'performance_rating',
        'is_new',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'supports_mobile' => 'boolean',
        'supports_desktop' => 'boolean',
        'supports_amp' => 'boolean',
        'gdpr_ready' => 'boolean',
        'is_new' => 'boolean',
        'ecpm_avg' => 'decimal:2',
        'fill_rate_avg' => 'decimal:2',
        'ctr_avg' => 'decimal:2',
        'performance_rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sizes()
    {
        return $this->hasMany(AdSize::class, 'format_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'format_id');
    }
}
