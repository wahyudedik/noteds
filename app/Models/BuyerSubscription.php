<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'billing_cycle',
        'price',
        'status',
        'started_at',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'payment_method',
        'midtrans_order_id',
        'midtrans_token',
        'payment_status',
        'gifted_by',
        'gifted_to',
        'is_gift',
        'gift_sent_at',
        'team_members',
        'billing_cycle_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'started_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'next_billing_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
            'is_gift' => 'boolean',
            'gift_sent_at' => 'datetime',
            'team_members' => 'array',
            'billing_cycle_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function giftedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gifted_by');
    }

    public function giftedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gifted_to');
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->current_period_end->isFuture()
            && !$this->isCancelled();
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' 
            || ($this->current_period_end && $this->current_period_end->isPast());
    }

    /**
     * Check if subscription can be renewed.
     */
    public function canRenew(): bool
    {
        return $this->auto_renew 
            && !$this->isCancelled() 
            && !$this->isExpired()
            && $this->status === 'active';
    }

    /**
     * Cancel subscription.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'auto_renew' => false,
        ]);
    }

    /**
     * Renew subscription.
     */
    public function renew(): void
    {
        $periodStart = $this->current_period_end;
        $periodEnd = $this->billing_cycle === 'monthly' 
            ? $periodStart->copy()->addMonth()
            : $periodStart->copy()->addYear();

        $this->update([
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'next_billing_date' => $periodEnd,
            'billing_cycle_count' => $this->billing_cycle_count + 1,
            'status' => 'active',
        ]);
    }

    /**
     * Get active subscription for user.
     */
    public static function activeForUser(string $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->whereNull('cancelled_at')
            ->latest('current_period_end')
            ->first();
    }

    /**
     * Calculate prorated amount for upgrade/downgrade.
     */
    public function calculateProratedAmount(SubscriptionPlan $newPlan, string $newBillingCycle): float
    {
        $daysRemaining = now()->diffInDays($this->current_period_end, false);
        $totalDays = $this->current_period_start->diffInDays($this->current_period_end);
        
        if ($daysRemaining <= 0) {
            return $newPlan->getPrice($newBillingCycle);
        }

        $remainingRatio = $daysRemaining / $totalDays;
        $remainingValue = $this->price * $remainingRatio;
        $newPrice = $newPlan->getPrice($newBillingCycle);
        
        return max(0, $newPrice - $remainingValue);
    }
}
