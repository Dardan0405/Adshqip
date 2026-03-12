<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'aq_users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'email',
        'password_hash',
        'role',
        'status',
        'preferred_language',
        'theme_preference',
        'timezone',
        'two_factor_enabled',
        'referral_code',
        'last_login_at',
        'last_login_ip',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    /**
     * Get the password attribute for Auth.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }
}
