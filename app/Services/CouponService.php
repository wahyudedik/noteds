<?php

namespace App\Services;

use App\Models\ProductCoupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate coupon code.
     */
    public function validateCoupon(string $code, User $user, Product $product = null, float $amount = 0): array
    {
        $coupon = ProductCoupon::where('code', $code)->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon code not found'];
        }

        if (!$coupon->isValid()) {
            return ['valid' => false, 'message' => 'Coupon is not valid or has expired'];
        }

        if (!$coupon->canBeUsedBy($user)) {
            return ['valid' => false, 'message' => 'You have reached the usage limit for this coupon'];
        }

        if ($coupon->product_id && $product && $coupon->product_id !== $product->id) {
            return ['valid' => false, 'message' => 'This coupon is not valid for this product'];
        }

        if ($coupon->min_purchase_amount && $amount < $coupon->min_purchase_amount) {
            return [
                'valid' => false,
                'message' => "Minimum purchase amount of {$coupon->min_purchase_amount} required"
            ];
        }

        $discount = $this->calculateDiscount($coupon, $amount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon is valid',
        ];
    }

    /**
     * Apply coupon to order.
     */
    public function applyCoupon(Order $order, string $code): CouponUsage
    {
        return DB::transaction(function () use ($order, $code) {
            $product = $order->product;
            $user = $order->user;
            $amount = (float) $order->total;

            $validation = $this->validateCoupon($code, $user, $product, $amount);

            if (!$validation['valid']) {
                throw new \Exception($validation['message']);
            }

            $coupon = $validation['coupon'];
            $discount = $validation['discount'];

            // Create coupon usage
            $usage = CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_amount' => $discount,
            ]);

            // Update order
            $order->update([
                'coupon_id' => $coupon->id,
                'discount_amount' => $discount,
                'total' => max(0, $amount - $discount),
            ]);

            // Increment coupon usage count
            $coupon->increment('usage_count');

            return $usage;
        });
    }

    /**
     * Calculate discount amount.
     */
    public function calculateDiscount(ProductCoupon $coupon, float $amount): float
    {
        return $coupon->calculateDiscount($amount);
    }
}

