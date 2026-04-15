<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileCapability extends Model
{
    use HasFactory;

    protected $table = 'aq_mobile_capabilities';

    protected $fillable = [
        'capability_name',
        'capability_value',
        'status',
    ];
}
