<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserContact extends Model
{
    protected $table = 'aq_advertiser_contacts';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company',
        'job_title',
        'type',
        'status',
        'is_primary',
        'notes',
        'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'last_contacted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
