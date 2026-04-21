<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTracking extends Model
{
    protected $table = 'aq_video_tracking';

    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'impression_id',
        'event_id',
        'viewer_id',
        'progress_percent',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}
