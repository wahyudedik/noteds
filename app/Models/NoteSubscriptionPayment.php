<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteSubscriptionPayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'subscription_id',
        'transaction_id',
        'amount',
        'status',
        'payment_method',
        'paid_at',
        'period_start',
        'period_end',
        'failure_reason',
        'attempt_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'attempt_number' => 'integer',
            'paid_at' => 'datetime',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(NoteSubscription::class, 'subscription_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

