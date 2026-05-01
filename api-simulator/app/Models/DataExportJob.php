<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataExportJob extends Model
{
    protected $table = 'aq_data_export_jobs';

    protected $fillable = [
        'created_by',
        'name',
        'dataset',
        'format',
        'status',
        'filters',
        'row_count',
        'file_path',
        'file_name',
        'file_size',
        'error_message',
        'started_at',
        'completed_at',
        'failed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'row_count' => 'integer',
            'file_size' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsDownloadableAttribute(): bool
    {
        return $this->status === 'completed' && filled($this->file_path);
    }
}
