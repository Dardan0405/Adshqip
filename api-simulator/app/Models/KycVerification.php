<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    protected $table = 'aq_kyc_verifications';

    protected $fillable = [
        'user_id',
        'verification_level',
        'status',
        'legal_first_name',
        'legal_last_name',
        'date_of_birth',
        'nationality',
        'id_number',
        'id_type',
        'id_issuing_country',
        'id_expiry_date',
        'business_name',
        'business_registration_number',
        'business_type',
        'business_country',
        'business_address',
        'vat_number',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
        'rejection_count',
        'notes',
        'risk_score',
        'risk_flags',
        'aml_check_passed',
        'sanctions_check_passed',
        'submitted_at',
        'approved_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'id_expiry_date' => 'date',
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'risk_flags' => 'array',
            'aml_check_passed' => 'boolean',
            'sanctions_check_passed' => 'boolean',
            'risk_score' => 'decimal:2',
            'id_number' => 'encrypted',
            'business_registration_number' => 'encrypted',
            'vat_number' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function documents()
    {
        return $this->hasMany(KycDocument::class, 'kyc_id');
    }
}
