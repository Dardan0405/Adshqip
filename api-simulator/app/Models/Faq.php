<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'aq_faq';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'category',
        'question',
        'answer',
        'language',
        'sort_order',
        'is_published',
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

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
