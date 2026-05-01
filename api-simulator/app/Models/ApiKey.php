<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $table = 'aq_api_keys';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'name',
        'api_key',
        'api_secret_hash',
        'api_secret_encrypted',
        'permissions',
        'rate_limit_per_minute',
        'allowed_ips',
        'status',
        'last_used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'allowed_ips' => 'array',
            'api_secret_encrypted' => 'encrypted',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
