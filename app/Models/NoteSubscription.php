<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'note_id',
        'user_id',
        'tier',
        'monthly_price',
        'status',
        'started_at',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'cancelled_at',
        'expires_at',
        'auto_renew',
        'cancellation_reason',
        'billing_cycle_count',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'auto_renew' => 'boolean',
            'billing_cycle_count' => 'integer',
            'started_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'next_billing_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(NoteSubscriptionPayment::class, 'subscription_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->current_period_end->isFuture()
            && !$this->isCancelled();
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' 
            || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if subscription can be renewed
     */
    public function canRenew(): bool
    {
        return $this->auto_renew 
            && !$this->isCancelled() 
            && !$this->isExpired()
            && $this->status === 'active';
    }

    /**
     * Check if user has access to note updates
     */
    public function hasAccess(): bool
    {
        return $this->isActive();
    }
}

