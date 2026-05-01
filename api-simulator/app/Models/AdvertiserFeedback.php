<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiserFeedback extends Model
{
    protected $table = 'aq_advertiser_feedback';

    protected $fillable = [
        'user_id',
        'type',
        'rating',
        'subject',
        'message',
        'page_url',
        'status',
        'admin_response',
        'reviewed_at',
        'closed_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
