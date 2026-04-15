<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectionType extends Model
{
    use HasFactory;

    protected $table = 'aq_connection_types';

    protected $fillable = [
        'connection_name',
        'connection_value',
        'status',
    ];
}
