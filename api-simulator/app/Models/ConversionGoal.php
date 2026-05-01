<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversionGoal extends Model
{
    use HasFactory;

    protected $table = 'aq_conversion_goals';

    protected $fillable = [
        'advertiser_id',
        'pixel_tracker_id',
        'name',
        'goal_key',
        'goal_type',
        'default_value',
        'currency',
        'counting_method',
        'attribution_window_days',
        'status',
        'description',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'default_value' => 'decimal:4',
            'attribution_window_days' => 'integer',
            'is_deleted' => 'boolean',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function pixelTracker()
    {
        return $this->belongsTo(PixelTracker::class, 'pixel_tracker_id');
    }
}
