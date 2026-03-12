<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'aq_user_profiles';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'company_name',
        'phone',
        'country_code',
        'website_url',
        'currency',
        'balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
