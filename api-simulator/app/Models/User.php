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
        'kyc_status',
        'kyc_level',
        'kyc_verified_at',
        'email_verified_at',
        'preferred_language',
        'theme_preference',
        'timezone',
        'two_factor_enabled',
        'two_factor_verification_options',
        'two_factor_token_types',
        'two_factor_phone',
        'two_factor_email',
        'two_factor_backup_question',
        'two_factor_backup_answer_hash',
        'two_factor_trusted_ip',
        'two_factor_trusted_subnet',
        'two_factor_trusted_browser',
        'two_factor_trusted_os',
        'two_factor_trusted_user_agent_hash',
        'referral_code',
        'last_login_at',
        'last_login_ip',
        'is_deleted',
        'security_question_id',
        'security_answer_hash',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'advertiser_id');
    }

    public function ads()
    {
        return $this->hasManyThrough(Ad::class, Campaign::class, 'advertiser_id', 'campaign_id');
    }

    public function sites()
    {
        return $this->hasMany(Site::class, 'publisher_id');
    }

    public function kycVerifications()
    {
        return $this->hasMany(KycVerification::class, 'user_id');
    }

    public function twoFactorBackupCodes()
    {
        return $this->hasMany(TwoFactorBackupCode::class, 'user_id');
    }

    public function referralPayouts()
    {
        return $this->hasMany(ReferralPayout::class, 'referrer_id');
    }

    public function securityQuestion()
    {
        return $this->belongsTo(SecurityQuestion::class, 'security_question_id');
    }

    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'role', 'role_key');
    }

    public function hasRolePermission(string $permission): bool
    {
        return (bool) $this->userRole?->hasPermission($permission);
    }

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
        'two_factor_backup_answer_hash',
        'two_factor_trusted_user_agent_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_verification_options' => 'array',
            'two_factor_token_types' => 'array',
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
