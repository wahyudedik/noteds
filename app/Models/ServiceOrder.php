<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ServiceOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'assigned_user_id',
        'title',
        'description',
        'budget',
        'status',
        'escrow_amount',
        'milestones',
        'work_status',
        'buyer_approval_status',
        'buyer_approved_at',
        'buyer_approval_notes',
        'admin_verified_by',
        'admin_verified_at',
        'admin_verification_notes',
        'release_request_status',
        'release_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'escrow_amount' => 'decimal:2',
            'milestones' => 'array',
            'buyer_approved_at' => 'datetime',
            'admin_verified_at' => 'datetime',
            'release_requested_at' => 'datetime',
        ];
    }

    // ============ Relationships ============

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function workSubmissions(): HasMany
    {
        return $this->hasMany(WorkSubmission::class);
    }

    public function latestWorkSubmission(): BelongsTo|null
    {
        return $this->belongsTo(WorkSubmission::class, 'id', 'service_order_id')
            ->latest('submitted_at');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function adminVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_verified_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)->latest();
    }

    public function escrowLedgers(): HasMany
    {
        return $this->hasMany(EscrowLedger::class);
    }

    public function workRevisions(): HasMany
    {
        return $this->hasMany(WorkRevision::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(ServiceOrderDispute::class);
    }

    public function activeDispute(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderDispute::class, 'active_dispute_id');
    }

    // ============ Methods for Permission Checks ============

    /**
     * Check if vendor can submit work
     */
    public function canVendorSubmitWork(?User $user = null): bool
    {
        $currentUser = $user;

        return $currentUser && $currentUser->id === $this->assigned_user_id
            && $this->status === 'in_progress'
            && $this->work_status !== 'submitted'
            && $this->escrow_amount > 0;
    }

    /**
     * Check if buyer can approve work
     */
    public function canBuyerApprove(?User $user = null): bool
    {
        $currentUser = $user;

        return $currentUser && $currentUser->id === $this->user_id
            && $this->work_status === 'submitted'
            && $this->buyer_approval_status === 'pending';
    }

    /**
     * Check if admin can verify and release payment
     */
    public function canAdminVerify(?User $user = null): bool
    {
        $currentUser = $user;

        return $currentUser && $currentUser->hasRole('admin')
            && $this->work_status === 'approved'
            && $this->buyer_approval_status === 'approved'
            && $this->escrow_amount > 0;
    }

    /**
     * Check if order has pending work submission
     */
    public function hasPendingWorkSubmission(): bool
    {
        return $this->work_status === 'submitted'
            && $this->buyer_approval_status === 'pending';
    }

    /**
     * Check if order is awaiting admin verification
     */
    public function isAwaitingAdminVerification(): bool
    {
        return $this->work_status === 'approved'
            && $this->buyer_approval_status === 'approved'
            && ! $this->admin_verified_at;
    }

    /**
     * Check if order is fully verified
     */
    public function isFullyVerified(): bool
    {
        return $this->admin_verified_at !== null
            && $this->admin_verification_notes !== null;
    }

    /**
     * Check if buyer can request revision
     */
    public function canBuyerRequestRevision(?User $user = null): bool
    {
        $currentUser = $user;

        return $currentUser && $currentUser->id === $this->user_id
            && $this->work_status === 'approved'
            && $this->buyer_approval_status === 'approved'
            && !$this->activeDispute
            && $this->getRemainingRevisions() > 0;
    }

    /**
     * Check if vendor can submit revision
     */
    public function canVendorSubmitRevision(?User $user = null): bool
    {
        $currentUser = $user;

        return $currentUser && $currentUser->id === $this->assigned_user_id
            && $this->revision_status === 'requested';
    }

    /**
     * Get remaining revisions allowed
     */
    public function getRemainingRevisions(): int
    {
        return $this->max_revisions - $this->revision_count;
    }

    /**
     * Get current pending revision
     */
    public function getCurrentPendingRevision(): ?WorkRevision
    {
        return $this->workRevisions()
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Get revision history
     */
    public function getRevisionHistory()
    {
        return $this->workRevisions()
            ->whereIn('status', ['submitted', 'accepted', 'rejected'])
            ->latest('created_at')
            ->get();
    }

    /**
     * Check if order has active dispute
     */
    public function hasActiveDispute(): bool
    {
        return $this->activeDispute !== null
            && !$this->activeDispute->isResolved();
    }

    /**
     * Get approval timeline
     */
    public function getApprovalTimeline(): array
    {
        return [
            'work_submitted_at' => $this->workSubmissions()->latest()->first()?->submitted_at,
            'buyer_approved_at' => $this->buyer_approved_at,
            'admin_verified_at' => $this->admin_verified_at,
            'payment_released_at' => $this->escrowLedgers()
                ->where('type', 'release')
                ->latest()
                ->first()?->created_at,
        ];
    }

    // ============ Scopes ============

    /**
     * Scope: where work is approved by buyer
     */
    public function scopeWhereWorkApproved($query)
    {
        return $query->where('work_status', 'approved')
            ->where('buyer_approval_status', 'approved');
    }

    /**
     * Scope: where awaiting admin verification
     */
    public function scopeWhereAwaitingAdminVerification($query)
    {
        return $query->whereWorkApproved()
            ->whereNull('admin_verified_at');
    }

    /**
     * Scope: where has pending work submission
     */
    public function scopeWherePendingWorkSubmission($query)
    {
        return $query->where('work_status', 'submitted')
            ->where('buyer_approval_status', 'pending');
    }

    /**
     * Scope: where work was rejected
     */
    public function scopeWhereWorkRejected($query)
    {
        return $query->where(function ($q) {
            $q->where('work_status', 'rejected')
                ->orWhere('buyer_approval_status', 'rejected');
        });
    }
}
