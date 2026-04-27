<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMiniApp extends Model
{
    protected $table = 'aq_telegram_mini_apps';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'application_type',
        'app_name',
        'app_short_name',
        'bot_username',
        'bot_token_hash',
        'app_url',
        'icon_url',
        'description',
        'category',
        'webhook_url',
        'webhook_secret_hash',
        'allowed_origins',
        'theme_params',
        'inline_mode_enabled',
        'payment_enabled',
        'payment_provider_token_hash',
        'menu_button_enabled',
        'expand_on_open',
        'status',
        'admin_approved',
        'rejection_reason',
        'total_sessions',
        'total_events',
        'last_active_at',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'theme_params' => 'array',
            'inline_mode_enabled' => 'boolean',
            'payment_enabled' => 'boolean',
            'menu_button_enabled' => 'boolean',
            'expand_on_open' => 'boolean',
            'admin_approved' => 'boolean',
            'is_deleted' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
