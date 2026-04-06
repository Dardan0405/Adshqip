<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSize extends Model
{
    use HasFactory;

    protected $table = 'aq_ad_sizes';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'width',
        'height',
        'format_id',
        'is_responsive',
        'status',
    ];

    protected $casts = [
        'is_responsive' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function format()
    {
        return $this->belongsTo(AdFormat::class, 'format_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'size_id');
    }
}
