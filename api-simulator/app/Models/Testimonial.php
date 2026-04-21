<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $table = 'aq_testimonials';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'author_name',
        'author_title',
        'author_company',
        'author_avatar_url',
        'quote',
        'rating',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id', 'desc');
    }
}
