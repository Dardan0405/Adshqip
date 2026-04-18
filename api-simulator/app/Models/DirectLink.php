<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DirectLink extends Model
{
    protected $table = 'aq_direct_links';

    protected $fillable = [
        'name',
        'publisher_scope',
        'publisher_ids',
        'adblock_scope',
        'zone_ids',
        'link_code',
        'destination_url',
        'click_count',
        'view_count',
        'status',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'publisher_ids' => 'array',
            'zone_ids' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateLinkCode(): string
    {
        do {
            $code = Str::random(16);
        } while (self::where('link_code', $code)->exists());

        return $code;
    }

    public function getFullUrlAttribute(): string
    {
        return url("/dl/{$this->link_code}");
    }

    public function getPublishersAttribute()
    {
        if ($this->publisher_scope === 'all') {
            return User::where('role', 'publisher')
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->get();
        }

        return User::whereIn('id', $this->publisher_ids ?? [])
            ->where('role', 'publisher')
            ->where('is_deleted', false)
            ->get();
    }

    public function getZonesAttribute()
    {
        if ($this->adblock_scope === 'all') {
            $query = Zone::where('is_deleted', false)
                ->where('status', 'active');

            if ($this->publisher_scope === 'selected' && !empty($this->publisher_ids)) {
                $query->whereHas('site', function ($q) {
                    $q->whereIn('publisher_id', $this->publisher_ids);
                });
            }

            return $query->get();
        }

        return Zone::whereIn('id', $this->zone_ids ?? [])
            ->where('is_deleted', false)
            ->get();
    }
}
