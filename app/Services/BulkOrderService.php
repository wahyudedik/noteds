<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCoupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BulkOrderService
{
    public function __construct(
        protected CouponService $couponService,
        protected MarketplaceService $marketplaceService
    ) {}

    /**
     * Create a single bulk order with multiple items.
     */
    public function createBulkOrder(array $items, User $user, ?string $couponCode = null): Order
    {
        $validationErrors = $this->validateBulkOrder($items);
        if (!empty($validationErrors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $validationErrors));
        }

        return DB::transaction(function () use ($items, $user, $couponCode) {
            $coupon = null;
            $discountAmount = 0;

            // Validate and apply coupon if provided
            if ($couponCode) {
                $total = $this->calculateBulkTotal($items, null);
                $validation = $this->couponService->validateCoupon($couponCode, $user, null, $total);
                
                if ($validation['valid']) {
                    $coupon = $validation['coupon'];
                    $discountAmount = $validation['discount'];
                }
            }

            $total = $this->calculateBulkTotal($items, $coupon);

            // Create bulk order
            $order = Order::create([
                'user_id' => $user->id,
                'product_id' => null, // Bulk orders don't have a single product
                'is_bulk_order' => true,
                'quantity' => 0, // Will be calculated from items
                'price' => 0, // Will be calculated from items
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'coupon_id' => $coupon?->id,
                'discount_amount' => $discountAmount,
            ]);

            // Create order items
            $orderIndex = 0;
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity'] ?? 1;
                $price = $product->price;
                $subtotal = $price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'order' => $orderIndex++,
                ]);

                // Decrement stock if applicable
                if ($product->stock !== null) {
                    $product->decrement('stock', $quantity);
                }
            }

            // Add initial tracking entry
            $order->addTracking('pending', 'pending', 'Bulk order created', $user);

            return $order->fresh(['items.product']);
        });
    }

    /**
     * Create multiple separate orders.
     */
    public function createMultipleOrders(array $items, User $user): Collection
    {
        $validationErrors = $this->validateBulkOrder($items);
        if (!empty($validationErrors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $validationErrors));
        }

        $orders = collect();

        DB::transaction(function () use ($items, $user, &$orders) {
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity'] ?? 1;

                $order = $this->marketplaceService->createOrder($product, $user->id, $quantity);
                $orders->push($order);

                // Add initial tracking entry
                $order->addTracking('pending', 'pending', 'Order created', $user);
            }
        });

        return $orders;
    }

    /**
     * Calculate total for bulk order.
     */
    public function calculateBulkTotal(array $items, ?ProductCoupon $coupon = null): float
    {
        $subtotal = 0;

        foreach ($items as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $quantity = $itemData['quantity'] ?? 1;
            $subtotal += (float) $product->price * $quantity;
        }

        $discount = 0;
        if ($coupon) {
            $discount = $coupon->calculateDiscount($subtotal);
        }

        return max(0, $subtotal - $discount);
    }

    /**
     * Validate bulk order items.
     */
    public function validateBulkOrder(array $items): array
    {
        $errors = [];

        if (empty($items)) {
            $errors[] = 'At least one item is required.';
            return $errors;
        }

        foreach ($items as $index => $item) {
            if (!isset($item['product_id'])) {
                $errors[] = "Item " . ($index + 1) . ": Product ID is required.";
                continue;
            }

            $product = Product::find($item['product_id']);
            if (!$product) {
                $errors[] = "Item " . ($index + 1) . ": Product not found.";
                continue;
            }

            if (!$product->is_active) {
                $errors[] = "Item " . ($index + 1) . ": Product '{$product->name}' is not available.";
            }

            $quantity = $item['quantity'] ?? 1;
            if ($quantity < 1) {
                $errors[] = "Item " . ($index + 1) . ": Quantity must be at least 1.";
            }

            if ($product->stock !== null && $product->stock < $quantity) {
                $errors[] = "Item " . ($index + 1) . ": Insufficient stock for '{$product->name}'. Available: {$product->stock}";
            }
        }

        return $errors;
    }
}

