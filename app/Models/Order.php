<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Order extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = (string) \Illuminate\Support\Str::uuid();
        }
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'status',
        'payment_status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'license_key',
        'platform_commission_percentage',
        'platform_commission_flat',
        'platform_commission_total',
        'seller_amount',
        'coupon_id',
        'discount_amount',
        'is_subscription_order',
        'subscription_id',
        'is_bulk_order',
        'tracking_enabled',
        'last_tracked_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'total' => 'decimal:2',
            'quantity' => 'integer',
            'platform_commission_percentage' => 'decimal:2',
            'platform_commission_flat' => 'decimal:2',
            'platform_commission_total' => 'decimal:2',
            'seller_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'is_subscription_order' => 'boolean',
            'is_bulk_order' => 'boolean',
            'tracking_enabled' => 'boolean',
            'last_tracked_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $orderNumber = "ORD-{$date}-{$random}";
        
        // Ensure uniqueness
        while (static::where('order_number', $orderNumber)->exists()) {
            $random = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $orderNumber = "ORD-{$date}-{$random}";
        }
        
        return $orderNumber;
    }

    /**
     * Get the buyer (user) that placed the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for buyer() for consistency with Laravel conventions.
     */
    public function user(): BelongsTo
    {
        return $this->buyer();
    }

    /**
     * Get the seller (user) that owns the product.
     */
    public function seller()
    {
        return $this->product?->seller;
    }

    /**
     * Get the product for the order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Mark order as paid.
     * Only updates payment_status, not order status.
     * Order status should be updated separately via markAsCompleted().
     */
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Mark order as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
        ]);
    }

    /**
     * Get the coupon used in this order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(ProductCoupon::class);
    }

    /**
     * Get the subscription for this order (if renewal).
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ProductSubscription::class);
    }

    /**
     * Calculate total with coupon discount.
     */
    public function calculateTotalWithCoupon(): float
    {
        $total = (float) $this->total;
        if ($this->discount_amount) {
            $total -= (float) $this->discount_amount;
        }
        return max(0, $total);
    }

    /**
     * Get order items (for bulk orders).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('order');
    }

    /**
     * Get tracking history.
     */
    public function trackingHistory(): HasMany
    {
        return $this->hasMany(OrderTrackingHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get order modifications.
     */
    public function modifications(): HasMany
    {
        return $this->hasMany(OrderModification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get user who cancelled the order.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Check if order is a bulk order.
     */
    public function isBulkOrder(): bool
    {
        return $this->is_bulk_order ?? false;
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->payment_status !== 'paid' && $this->status !== 'cancelled';
    }

    /**
     * Check if order can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->payment_status !== 'paid' && $this->status !== 'cancelled';
    }

    /**
     * Add tracking entry.
     */
    public function addTracking(string $status, ?string $paymentStatus = null, ?string $message = null, ?User $updatedBy = null): OrderTrackingHistory
    {
        $tracking = OrderTrackingHistory::create([
            'order_id' => $this->id,
            'status' => $status,
            'payment_status' => $paymentStatus ?? $this->payment_status,
            'message' => $message,
            'updated_by' => $updatedBy?->id,
        ]);

        $this->update([
            'status' => $status,
            'payment_status' => $paymentStatus ?? $this->payment_status,
            'last_tracked_at' => now(),
        ]);

        return $tracking;
    }

    /**
     * Get latest tracking entry.
     */
    public function getLatestTracking(): ?OrderTrackingHistory
    {
        return $this->trackingHistory()->first();
    }

    /**
     * Get tracking timeline (all tracking entries).
     */
    public function getTrackingTimeline(): Collection
    {
        return $this->trackingHistory()->with('updatedBy')->get();
    }

    /**
     * Scope: Filter bulk orders.
     */
    public function scopeBulk(Builder $query): Builder
    {
        return $query->where('is_bulk_order', true);
    }

    /**
     * Scope: Filter trackable orders.
     */
    public function scopeTrackable(Builder $query): Builder
    {
        return $query->where('tracking_enabled', true);
    }

    /**
     * Scope: Filter cancellable orders.
     */
    public function scopeCancellable(Builder $query): Builder
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope: Filter modifiable orders.
     */
    public function scopeModifiable(Builder $query): Builder
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');
    }
}
