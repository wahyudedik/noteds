<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductSubscription extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) Str::uuid();
        }
    }

    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'status',
        'current_cycle',
        'total_cycles',
        'next_billing_date',
        'last_billing_date',
        'trial_ends_at',
        'cancelled_at',
        'midtrans_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'current_cycle' => 'integer',
            'total_cycles' => 'integer',
            'next_billing_date' => 'datetime',
            'last_billing_date' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(SubscriptionRenewal::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if in trial period.
     */
    public function isTrial(): bool
    {
        return $this->trial_ends_at && Carbon::now()->lt($this->trial_ends_at);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Pause subscription.
     */
    public function pause(): void
    {
        if ($this->isActive()) {
            $this->update(['status' => 'paused']);
        }
    }

    /**
     * Resume subscription.
     */
    public function resume(): void
    {
        if ($this->status === 'paused') {
            $this->update(['status' => 'active']);
        }
    }

    /**
     * Calculate next renewal date.
     */
    public function nextRenewalDate(): Carbon
    {
        $interval = $this->product->subscription_interval ?? 'monthly';
        
        return match ($interval) {
            'daily' => Carbon::now()->addDay(),
            'weekly' => Carbon::now()->addWeek(),
            'monthly' => Carbon::now()->addMonth(),
            'yearly' => Carbon::now()->addYear(),
            default => Carbon::now()->addMonth(),
        };
    }

    /**
     * Scope to filter active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to filter expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope to filter subscriptions due for renewal.
     */
    public function scopeDueForRenewal($query)
    {
        return $query->where('status', 'active')
            ->where('next_billing_date', '<=', now());
    }
}
