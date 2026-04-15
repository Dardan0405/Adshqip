<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;

    protected $table = 'aq_keywords';

    protected $fillable = [
        'keyword',
        'category',
        'description',
        'status',
        'match_count',
        'last_matched_at',
    ];

    protected function casts(): array
    {
        return [
            'last_matched_at' => 'datetime',
        ];
    }

    /**
     * Scope to get only active keywords.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Increment the match count when this keyword is detected on a website.
     */
    public function recordMatch(): void
    {
        $this->increment('match_count');
        $this->update(['last_matched_at' => now()]);
    }

    /**
     * Check if a given string contains this keyword (case-insensitive).
     */
    public function matchesContent(string $content): bool
    {
        return stripos($content, $this->keyword) !== false;
    }

    /**
     * Get campaigns that target this keyword.
     * Note: This is a JSON contains search, works in MySQL 5.7+/MariaDB 10.2+.
     */
    public function getCampaignsAttribute()
    {
        return Campaign::whereJsonContains('targeting_keywords', $this->keyword)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->get();
    }
}
