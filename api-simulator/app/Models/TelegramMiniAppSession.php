<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMiniAppSession extends Model
{
    protected $table = 'aq_telegram_mini_app_sessions';

    const UPDATED_AT = null;

    protected $fillable = [
        'mini_app_id',
        'telegram_user_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_last_name',
        'telegram_language_code',
        'telegram_is_premium',
        'auth_date',
        'init_data_hash',
        'query_id',
        'platform',
        'ip_address',
        'user_agent',
        'start_param',
        'referrer_mini_app_id',
        'duration_seconds',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'telegram_is_premium' => 'boolean',
            'auth_date' => 'datetime',
            'created_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
