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
        'gender',
        'date_of_birth',
        'mobile_number',
        'skype_address',
        'icq_address',
        'jabber_address',
        'alternative_email',
        'company_name',
        'company_address_line1',
        'company_address_line2',
        'company_city',
        'company_state_region',
        'company_country_code',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state_region',
        'country_code',
        'postal_code',
        'vat_number',
        'website_url',
        'avatar_url',
        'currency',
        'payment_method',
        'payment_details',
        'payout_method',
        'payout_details',
        'notification_settings',
        'balance',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'payment_details' => 'array',
        'payout_details' => 'array',
        'notification_settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
