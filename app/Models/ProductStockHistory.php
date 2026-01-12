<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductStockHistory extends Model
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
        'product_id',
        'change_type',
        'quantity_change',
        'quantity_before',
        'quantity_after',
        'reason',
        'order_id',
        'updated_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the product for this stock history entry.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order associated with this stock change.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made this stock change.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Record a sale stock change.
     */
    public static function recordSale(Product $product, Order $order, int $quantity): self
    {
        return self::create([
            'product_id' => $product->id,
            'change_type' => 'sale',
            'quantity_change' => -$quantity,
            'quantity_before' => $product->stock + $quantity,
            'quantity_after' => $product->stock,
            'order_id' => $order->id,
            'reason' => 'Order #' . $order->order_number,
        ]);
    }

    /**
     * Record a restock.
     */
    public static function recordRestock(Product $product, int $quantity, ?string $reason = null, ?User $updatedBy = null): self
    {
        return self::create([
            'product_id' => $product->id,
            'change_type' => 'restock',
            'quantity_change' => $quantity,
            'quantity_before' => $product->stock,
            'quantity_after' => $product->stock + $quantity,
            'reason' => $reason ?? 'Manual restock',
            'updated_by' => $updatedBy?->id,
        ]);
    }

    /**
     * Record a manual adjustment.
     */
    public static function recordAdjustment(Product $product, int $newQuantity, string $reason, ?User $updatedBy = null): self
    {
        $quantityChange = $newQuantity - $product->stock;

        return self::create([
            'product_id' => $product->id,
            'change_type' => 'adjustment',
            'quantity_change' => $quantityChange,
            'quantity_before' => $product->stock,
            'quantity_after' => $newQuantity,
            'reason' => $reason,
            'updated_by' => $updatedBy?->id,
        ]);
    }

    /**
     * Scope a query to only include history for a specific product.
     */
    public function scopeForProduct(Builder $query, string $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include history of a specific type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('change_type', $type);
    }

    /**
     * Scope a query to only include recent history.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
