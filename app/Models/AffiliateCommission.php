<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'affiliate_id',
        'conversion_id',
        'transaction_id',
        'tier',
        'parent_affiliate_id',
        'commission_rate',
        'commission_amount',
        'transaction_amount',
        'status',
        'approved_at',
        'paid_at',
        'payout_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tier' => 'integer',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'transaction_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function parentAffiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_affiliate_id');
    }

    public function conversion(): BelongsTo
    {
        return $this->belongsTo(AffiliateConversion::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class);
    }

    /**
     * Scope for pending commissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved commissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid commissions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for specific tier.
     */
    public function scopeTier($query, int $tier)
    {
        return $query->where('tier', $tier);
    }
}
