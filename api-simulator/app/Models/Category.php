<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'aq_categories';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'iab_code',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'aq_campaign_category', 'category_id', 'campaign_id');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'aq_site_categories', 'category_id', 'site_id');
    }
}
