<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Browser extends Model
{
    use HasFactory;

    protected $table = 'aq_browsers';

    protected $fillable = [
        'browser_name',
        'browser_code',
        'status',
    ];
}
