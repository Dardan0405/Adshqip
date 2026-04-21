<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSession extends Model
{
    protected $table = 'aq_sessions';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'user_agent',
        'browser',
        'os',
        'device_type',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
