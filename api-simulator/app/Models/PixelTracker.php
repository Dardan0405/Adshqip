<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PixelTracker extends Model
{
    use HasFactory;

    protected $table = 'aq_pixel_trackers';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'description',
        'advertiser_id',
        'type',
        'pixel_code',
        'tracking_url',
        'parameters',
        'is_active',
        'fire_count',
        'last_fired_at',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'is_active' => 'boolean',
            'is_deleted' => 'boolean',
            'fire_count' => 'integer',
            'last_fired_at' => 'datetime',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }
}
