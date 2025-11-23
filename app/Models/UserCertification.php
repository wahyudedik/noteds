<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCertification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'certification_id',
        'status',
        'application_notes',
        'admin_notes',
        'approved_by',
        'applied_at',
        'approved_at',
        'rejected_at',
        'expires_at',
        'evidence',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'applied_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class, 'certification_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if certification is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if certification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if certification is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if certification is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }

    /**
     * Check if certification is active (approved and not expired)
     */
    public function isActive(): bool
    {
        return $this->isApproved() && !$this->isExpired();
    }
}

