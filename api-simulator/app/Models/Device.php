<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'aq_devices';

    protected $fillable = [
        'device_name',
        'device_value',
        'status',
    ];
}
