<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrafficSourceLookup extends Model
{
    protected $table = 'aq_traffic_sources';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];
}
