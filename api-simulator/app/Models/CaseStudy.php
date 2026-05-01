<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $table = 'aq_case_studies';

    protected $fillable = [
        'slug',
        'title',
        'audience_type',
        'industry',
        'metric_value',
        'metric_label',
        'description',
        'content',
        'company_name',
        'client_name',
        'logo_url',
        'accent_color',
        'chart_type',
        'before_label',
        'before_value',
        'after_label',
        'after_value',
        'cta_url',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
