<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrowserLanguage extends Model
{
    use HasFactory;

    protected $table = 'aq_browser_languages';

    protected $fillable = [
        'language_name',
        'language_value',
        'status',
    ];
}
