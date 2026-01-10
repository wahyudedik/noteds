<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderModification;
use App\Models\Product;
use App\Models\ProductCoupon;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Support\Facades\DB;

class OrderModificationService
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Modify an order.
     */
    public function modifyOrder(Order $order, array $changes, User $user, ?string $reason = null): Order
    {
        if (!$order->canBeModified()) {
            throw new \Exception('Order cannot be modified.');
        }

        $validationErrors = $this->validateModification($order, $changes);
        if (!empty($validationErrors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $validationErrors));
        }

        return DB::transaction(function () use ($order, $changes, $user, $reason) {
            $oldData = [
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'price' => $order->price,
                'total' => $order->total,
                'coupon_id' => $order->coupon_id,
                'discount_amount' => $order->discount_amount,
            ];

            $modificationType = 'quantity';
            if (isset($changes['product_id'])) {
                $modificationType = isset($changes['quantity']) || isset($changes['coupon_code']) ? 'all' : 'product';
            } elseif (isset($changes['coupon_code'])) {
                $modificationType = 'coupon';
            }

            // Modify quantity
            if (isset($changes['quantity'])) {
                $order = $this->modifyQuantity($order, $changes['quantity'], $user, false);
            }

            // Modify product
            if (isset($changes['product_id'])) {
                $newProduct = Product::findOrFail($changes['product_id']);
                $order = $this->modifyProduct($order, $newProduct, $user, false);
            }

            // Modify coupon
            if (isset($changes['coupon_code'])) {
                $order = $this->modifyCoupon($order, $changes['coupon_code'], $user, false);
            } elseif (isset($changes['coupon_code']) && $changes['coupon_code'] === null) {
                // Remove coupon
                $order->update([
                    'coupon_id' => null,
                    'discount_amount' => null,
                ]);
                $this->recalculateTotal($order);
            }

            // Record modification
            $newData = [
                'product_id' => $order->product_id,
                'quantity' => $order->quantity,
                'price' => $order->price,
                'total' => $order->total,
                'coupon_id' => $order->coupon_id,
                'discount_amount' => $order->discount_amount,
            ];

            OrderModification::create([
                'order_id' => $order->id,
                'modification_type' => $modificationType,
                'old_data' => $oldData,
                'new_data' => $newData,
                'modified_by' => $user->id,
                'reason' => $reason,
            ]);

            // Add tracking entry
            $order->addTracking('pending', null, 'Order modified', $user);

            return $order->fresh();
        });
    }

    /**
     * Modify order quantity.
     */
    public function modifyQuantity(Order $order, int $quantity, User $user, bool $recordModification = true): Order
    {
        if (!$order->canBeModified()) {
            throw new \Exception('Order cannot be modified.');
        }

        if ($quantity < 1) {
            throw new \Exception('Quantity must be at least 1.');
        }

        // Check stock if product has stock management
        if ($order->product && $order->product->stock !== null) {
            $availableStock = $order->product->stock + $order->quantity; // Add back current quantity
            if ($quantity > $availableStock) {
                throw new \Exception('Insufficient stock. Available: ' . $availableStock);
            }
        }

        return DB::transaction(function () use ($order, $quantity, $user, $recordModification) {
            $oldData = [
                'quantity' => $order->quantity,
                'total' => $order->total,
            ];

            $order->update(['quantity' => $quantity]);
            $this->recalculateTotal($order);

            if ($recordModification) {
                OrderModification::create([
                    'order_id' => $order->id,
                    'modification_type' => 'quantity',
                    'old_data' => $oldData,
                    'new_data' => [
                        'quantity' => $order->quantity,
                        'total' => $order->total,
                    ],
                    'modified_by' => $user->id,
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * Modify order product.
     */
    public function modifyProduct(Order $order, Product $newProduct, User $user, bool $recordModification = true): Order
    {
        if (!$order->canBeModified()) {
            throw new \Exception('Order cannot be modified.');
        }

        if (!$newProduct->is_active) {
            throw new \Exception('Product is not available.');
        }

        // Check stock
        if ($newProduct->stock !== null && $newProduct->stock < $order->quantity) {
            throw new \Exception('Insufficient stock for selected product.');
        }

        return DB::transaction(function () use ($order, $newProduct, $user, $recordModification) {
            $oldData = [
                'product_id' => $order->product_id,
                'price' => $order->price,
                'total' => $order->total,
            ];

            $order->update([
                'product_id' => $newProduct->id,
                'price' => $newProduct->price,
            ]);
            $this->recalculateTotal($order);

            if ($recordModification) {
                OrderModification::create([
                    'order_id' => $order->id,
                    'modification_type' => 'product',
                    'old_data' => $oldData,
                    'new_data' => [
                        'product_id' => $order->product_id,
                        'price' => $order->price,
                        'total' => $order->total,
                    ],
                    'modified_by' => $user->id,
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * Modify order coupon.
     */
    public function modifyCoupon(Order $order, ?string $couponCode, User $user, bool $recordModification = true): Order
    {
        if (!$order->canBeModified()) {
            throw new \Exception('Order cannot be modified.');
        }

        return DB::transaction(function () use ($order, $couponCode, $user, $recordModification) {
            $oldData = [
                'coupon_id' => $order->coupon_id,
                'discount_amount' => $order->discount_amount,
                'total' => $order->total,
            ];

            if ($couponCode) {
                $validation = $this->couponService->validateCoupon(
                    $couponCode,
                    $user,
                    $order->product,
                    $order->total
                );

                if (!$validation['valid']) {
                    throw new \Exception($validation['message'] ?? 'Invalid or expired coupon.');
                }

                $coupon = $validation['coupon'];
                $discountAmount = $validation['discount'];

                $order->update([
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discountAmount,
                ]);
            } else {
                $order->update([
                    'coupon_id' => null,
                    'discount_amount' => null,
                ]);
            }

            $this->recalculateTotal($order);

            if ($recordModification) {
                OrderModification::create([
                    'order_id' => $order->id,
                    'modification_type' => 'coupon',
                    'old_data' => $oldData,
                    'new_data' => [
                        'coupon_id' => $order->coupon_id,
                        'discount_amount' => $order->discount_amount,
                        'total' => $order->total,
                    ],
                    'modified_by' => $user->id,
                ]);
            }

            return $order->fresh();
        });
    }

    /**
     * Validate modification changes.
     */
    public function validateModification(Order $order, array $changes): array
    {
        $errors = [];

        if (isset($changes['quantity']) && $changes['quantity'] < 1) {
            $errors[] = 'Quantity must be at least 1.';
        }

        if (isset($changes['product_id'])) {
            $product = Product::find($changes['product_id']);
            if (!$product) {
                $errors[] = 'Product not found.';
            } elseif (!$product->is_active) {
                $errors[] = 'Product is not available.';
            } elseif ($product->stock !== null && isset($changes['quantity'])) {
                $quantity = $changes['quantity'];
                $availableStock = $product->stock + ($order->product_id === $product->id ? $order->quantity : 0);
                if ($quantity > $availableStock) {
                    $errors[] = 'Insufficient stock.';
                }
            }
        }

        return $errors;
    }

    /**
     * Recalculate order total.
     */
    protected function recalculateTotal(Order $order): void
    {
        $subtotal = (float) $order->price * (int) $order->quantity;
        $discountAmount = (float) ($order->discount_amount ?? 0);
        $total = max(0, $subtotal - $discountAmount);

        $order->update(['total' => $total]);
    }
}

