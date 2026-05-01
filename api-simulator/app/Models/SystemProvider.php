<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemProvider extends Model
{
    use HasFactory;

    protected $table = 'aq_system_providers';

    protected $fillable = [
        'name',
        'slug',
        'provider_type',
        'environment',
        'status',
        'source',
        'source_key',
        'base_url',
        'webhook_url',
        'auth_type',
        'api_key',
        'api_secret',
        'priority',
        'timeout_seconds',
        'config',
        'last_check_status',
        'last_check_message',
        'last_checked_at',
        'last_used_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'api_secret' => 'encrypted',
            'config' => 'array',
            'last_checked_at' => 'datetime',
            'last_used_at' => 'datetime',
            'priority' => 'integer',
            'timeout_seconds' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSource($query, string $source, ?string $sourceKey = null)
    {
        return $query->where('source', $source)
            ->when($sourceKey !== null, fn ($innerQuery) => $innerQuery->where('source_key', $sourceKey));
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config ?? [], $key, $default);
    }

    public function markUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }
}
