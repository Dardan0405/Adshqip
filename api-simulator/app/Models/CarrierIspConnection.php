<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierIspConnection extends Model
{
    use HasFactory;

    protected $table = 'aq_carrier_isp_connections';

    protected $fillable = [
        'carrier_name',
        'start_ip',
        'end_ip',
        'country',
        'status',
    ];
}
