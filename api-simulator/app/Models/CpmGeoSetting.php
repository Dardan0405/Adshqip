<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpmGeoSetting extends Model
{
    protected $table = 'aq_cpm_geo_settings';

    protected $fillable = [
        'country_code',
        'country_name',
        'cpm_value',
        'created_by',
    ];

    protected $casts = [
        'cpm_value' => 'decimal:4',
    ];
}
