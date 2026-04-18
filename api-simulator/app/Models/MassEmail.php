<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MassEmail extends Model
{
    protected $table = 'aq_mass_emails';

    protected $fillable = [
        'recipient_type',
        'recipient_scope',
        'recipient_ids',
        'subject',
        'message',
        'recipients_count',
        'sent_count',
        'failed_count',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recipient_ids' => 'array',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
