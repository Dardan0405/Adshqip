<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'aq_sites';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'publisher_id',
        'name',
        'domain',
        'description',
        'category',
        'language',
        'monthly_pageviews',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'site_id');
    }

    public function activeZones()
    {
        return $this->hasMany(Zone::class, 'site_id')->where('status', 'active')->where('is_deleted', false);
    }
}
