<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model
{
    protected $table = 'aq_admin_messages';

    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'message',
        'priority',
        'is_read',
        'read_at',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_archived' => 'boolean',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }
}
