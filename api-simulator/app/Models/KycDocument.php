<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    protected $table = 'aq_kyc_documents';

    protected $fillable = [
        'kyc_id',
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function verification()
    {
        return $this->belongsTo(KycVerification::class, 'kyc_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
