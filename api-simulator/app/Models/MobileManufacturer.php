<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileManufacturer extends Model
{
    use HasFactory;

    protected $table = 'aq_mobile_manufacturers';

    protected $fillable = [
        'manufacturer_name',
        'manufacturer_value',
        'status',
    ];
}
