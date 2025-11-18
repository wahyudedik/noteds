<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteSharePurchase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'share_referral_id',
        'transaction_id',
        'buyer_id',
        'purchase_amount',
        'commission_amount',
        'commission_status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_status' => 'string',
        ];
    }

    /**
     * Get the share referral that generated this purchase.
     */
    public function shareReferral(): BelongsTo
    {
        return $this->belongsTo(NoteShareReferral::class, 'share_referral_id');
    }

    /**
     * Get the transaction for this purchase.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the buyer who made the purchase.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Mark commission as paid.
     */
    public function markAsPaid(): void
    {
        $this->update(['commission_status' => 'paid']);
    }

    /**
     * Mark commission as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['commission_status' => 'cancelled']);
    }
}
