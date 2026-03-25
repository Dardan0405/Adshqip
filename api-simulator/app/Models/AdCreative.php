<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdCreative extends Model
{
    use HasFactory;

    protected $table = 'aq_ad_creatives';

    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'file_path',
        'video_url',
        'file_type',
        'mime_type',
        'file_size_bytes',
        'width',
        'height',
        'duration_seconds',
        'alt_text',
        'thumbnail_path',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function getSizeAttribute(): string
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        return '—';
    }

    public function getFileSizeKbAttribute(): string
    {
        if ($this->file_size_bytes) {
            return number_format($this->file_size_bytes / 1024, 1);
        }
        return '—';
    }
}
