<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirusScan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'scan_status',
        'scan_result',
        'threat_name',
        'threat_details',
        'quarantine_path',
        'is_quarantined',
        'quarantined_at',
        'scanned_by_user_id',
        'note_id',
        'scan_type',
        'scan_duration_ms',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_quarantined' => 'boolean',
            'scan_duration_ms' => 'integer',
            'quarantined_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Check if scan is clean
     */
    public function isClean(): bool
    {
        return $this->scan_status === 'clean';
    }

    /**
     * Check if scan detected infection
     */
    public function isInfected(): bool
    {
        return $this->scan_status === 'infected';
    }

    /**
     * Check if file is quarantined
     */
    public function isQuarantined(): bool
    {
        return $this->is_quarantined && $this->quarantine_path !== null;
    }

    /**
     * Check if scan is pending
     */
    public function isPending(): bool
    {
        return $this->scan_status === 'pending';
    }

    /**
     * Check if scan has error
     */
    public function hasError(): bool
    {
        return $this->scan_status === 'error';
    }
}


