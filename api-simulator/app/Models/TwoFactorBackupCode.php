<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorBackupCode extends Model
{
    public $timestamps = false;

    protected $table = 'aq_two_factor_backup_codes';

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'user_id',
        'code_hash',
        'used',
        'used_at',
        'used_ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
