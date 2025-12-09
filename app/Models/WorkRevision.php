<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkRevision extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'service_order_id',
        'revision_number',
        'requested_by',
        'request_reason',
        'status',
        'submission_notes',
        'submitted_at',
        'submitted_by',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to ServiceOrder
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Relationship: Requested by User (buyer or vendor)
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relationship: Submitted by User (vendor)
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship: Rejected by User (buyer)
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Check if revision is pending (waiting for vendor to submit)
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if revision is submitted (waiting for buyer approval)
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if revision is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if revision is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get revision status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Revisi',
            'submitted' => 'Revisi Dikirim',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => 'Unknown'
        };
    }
}
