<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlatformAnnouncement extends Model
{
    protected $table = 'aq_platform_announcements';

    protected $fillable = [
        'title',
        'slug',
        'audience',
        'placement',
        'type',
        'status',
        'summary',
        'body',
        'cta_label',
        'cta_url',
        'starts_at',
        'ends_at',
        'published_at',
        'created_by',
        'is_pinned',
        'notification_count',
        'last_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'is_pinned' => 'boolean',
            'notification_count' => 'integer',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $nested) {
                $nested->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $nested) {
                $nested->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForAudience(Builder $query, string $audience): Builder
    {
        return $query->where(function (Builder $nested) use ($audience) {
            $nested->where('audience', 'all')->orWhere('audience', $audience);
        });
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
