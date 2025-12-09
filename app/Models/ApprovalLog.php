<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    protected $fillable = [
        'service_order_id',
        'approver_id',
        'approver_type',
        'action',
        'notes',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the service order that this approval log belongs to
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Get the user who performed the approval
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get human-readable approver type label
     */
    public function getApproverTypeLabel(): string
    {
        return match ($this->approver_type) {
            'buyer' => 'Buyer',
            'admin' => 'Admin',
            default => ucfirst($this->approver_type),
        };
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabel(): string
    {
        return match ($this->action) {
            'work_submitted' => 'Work Submitted',
            'work_approved' => 'Work Approved',
            'work_rejected' => 'Work Rejected',
            'payment_released' => 'Payment Released',
            'payment_rejected' => 'Payment Rejected',
            'refund_issued' => 'Refund Issued',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get timeline display string
     */
    public function getTimelineDisplay(): string
    {
        return sprintf(
            '%s - %s by %s (%s)',
            $this->action_at->format('d M Y H:i'),
            $this->getActionLabel(),
            $this->approver?->name ?? 'Unknown',
            $this->getApproverTypeLabel()
        );
    }
}
