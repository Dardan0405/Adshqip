<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingSystem extends Model
{
    use HasFactory;

    protected $table = 'aq_operating_systems';

    protected $fillable = [
        'os_name',
        'os_value',
        'status',
        'devices',
    ];

    protected function casts(): array
    {
        return [
            'devices' => 'array',
        ];
    }
}
