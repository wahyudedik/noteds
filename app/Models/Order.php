<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
