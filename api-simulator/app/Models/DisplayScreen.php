<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisplayScreen extends Model
{
    protected $table = 'aq_display_screens';

    protected $fillable = [
        'screen_name',
        'value',
        'width',
        'height',
        'status',
        'created_by',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function getDimensionsAttribute(): string
    {
        return $this->width . ' x ' . $this->height;
    }

    public function creatives()
    {
        return $this->hasMany(AdCreative::class, 'display_screen_id');
    }
}
