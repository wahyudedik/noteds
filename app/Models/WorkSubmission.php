<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSubmission extends Model
{
    protected $fillable = [
        'service_order_id',
        'vendor_id',
        'status',
        'description',
        'files',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'files' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the service order this submission belongs to
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Get the vendor who submitted the work
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get the user who approved this submission
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if submission is approved by buyer
     */
    public function isApprovedByBuyer(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if submission was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get file count
     */
    public function getFileCount(): int
    {
        return is_array($this->files) ? count($this->files) : 0;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }
}
