<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_id',
        'buyer_id',
        'seller_id',
        'note_id',
        'amount',
        'escrow_fee',
        'platform_fee',
        'status',
        'funded_at',
        'auto_release_at',
        'auto_release_days',
        'released_at',
        'refunded_at',
        'released_by',
        'refunded_by',
        'release_notes',
        'refund_reason',
        'dispute_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'escrow_fee' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'auto_release_days' => 'integer',
            'funded_at' => 'datetime',
            'auto_release_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function refunder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    /**
     * Check if escrow is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if escrow is funded
     */
    public function isFunded(): bool
    {
        return $this->status === 'funded';
    }

    /**
     * Check if escrow is released
     */
    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    /**
     * Check if escrow is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if escrow is disputed
     */
    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }

    /**
     * Check if escrow can be auto-released
     */
    public function canAutoRelease(): bool
    {
        return $this->isFunded() 
            && $this->auto_release_at 
            && $this->auto_release_at->isPast()
            && !$this->isDisputed();
    }

    /**
     * Get seller payout amount (after fees)
     */
    public function getSellerPayoutAmount(): float
    {
        return $this->amount - $this->escrow_fee - $this->platform_fee;
    }
}

