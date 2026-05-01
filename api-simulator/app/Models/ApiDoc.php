<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiDoc extends Model
{
    protected $table = 'aq_api_docs';

    protected $fillable = [
        'slug',
        'title',
        'category',
        'http_method',
        'endpoint_path',
        'auth_required',
        'required_permission',
        'description',
        'headers_example',
        'request_example',
        'response_example',
        'notes',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'auth_required' => 'boolean',
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
