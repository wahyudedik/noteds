<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderDispute extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'service_order_id',
        'initiated_by',
        'reason',
        'status',
        'resolution',
        'resolution_type',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to ServiceOrder
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Relationship: Initiated by User (buyer or vendor)
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Relationship: Resolved by User (admin)
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Relationship: Evidence files
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class, 'dispute_id');
    }

    /**
     * Check if dispute is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if dispute is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Check if dispute is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if dispute is escalated
     */
    public function isEscalated(): bool
    {
        return $this->status === 'escalated';
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Terbuka',
            'under_review' => 'Sedang Ditinjau',
            'resolved' => 'Terselesaikan',
            'escalated' => 'Ditingkatkan',
            default => 'Unknown'
        };
    }

    /**
     * Get resolution type label
     */
    public function getResolutionTypeLabel(): string
    {
        return match($this->resolution_type) {
            'refund_buyer' => 'Refund ke Buyer',
            'payment_vendor' => 'Bayar Vendor',
            'partial_refund' => 'Refund Partial',
            'custom' => 'Custom Resolution',
            default => 'Unknown'
        };
    }
}
