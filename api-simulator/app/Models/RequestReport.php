<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestReport extends Model
{
    protected $table = 'aq_request_reports';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'frequency',
        'report_type',
        'file_format',
        'display_by',
        'environment',
        'metrics',
        'dimensions',
        'filters',
        'status',
        'is_deleted',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'dimensions' => 'array',
            'filters' => 'array',
            'is_deleted' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
