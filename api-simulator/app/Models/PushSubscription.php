<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $table = 'aq_push_subscriptions';

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'endpoint',
        'endpoint_hash',
        'p256dh_key',
        'auth_token',
        'user_agent',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
