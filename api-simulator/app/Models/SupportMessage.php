<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $table = 'aq_support_messages';

    const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'message',
        'attachment_url',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'is_internal_note' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
