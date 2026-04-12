<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityQuestion extends Model
{
    protected $table = 'aq_security_questions';

    protected $fillable = [
        'question',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
