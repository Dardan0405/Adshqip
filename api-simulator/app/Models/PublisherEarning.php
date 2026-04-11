<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherEarning extends Model
{
    protected $table = 'aq_stats_daily';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'site_id',
        'publisher_id',
        'impressions',
        'clicks',
        'conversions',
        'revenue',
        'publisher_earnings',
        'ecpm',
        'ctr',
        'fill_rate',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'publisher_earnings' => 'decimal:4',
            'revenue' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function scopeForPublisher($query, int $publisherId)
    {
        return $query->where('aq_stats_daily.publisher_id', $publisherId);
    }

    public function scopeBetweenMonths($query, ?string $startMonth, ?string $endMonth)
    {
        if ($startMonth) {
            $query->whereDate('aq_stats_daily.date', '>=', $startMonth . '-01');
        }

        if ($endMonth) {
            $query->whereDate('aq_stats_daily.date', '<=', date('Y-m-t', strtotime($endMonth . '-01')));
        }

        return $query;
    }
}
