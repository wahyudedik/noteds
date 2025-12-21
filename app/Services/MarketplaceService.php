<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class MarketplaceService
{
    /**
     * Create order and initiate payment.
     */
    public function createOrder(Product $product, string $userId, int $quantity = 1): Order
    {
        if (!$product->is_active) {
            throw new \Exception('Product is not available');
        }

        if ($product->stock !== null && $product->stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $total = $product->price * $quantity;

        $order = Order::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'total' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        return $order;
    }

    /**
     * Complete order after payment.
     */
    public function completeOrder(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            $order->markAsPaid();
        }

        // Generate license key for tracking
        $licenseKey = $this->generateLicenseKey($order);
        $order->update(['license_key' => $licenseKey]);

        // Update product sales count
        $order->product->increment('sales_count');

        // Update stock if applicable
        if ($order->product->stock !== null) {
            $order->product->decrement('stock', $order->quantity);
        }

        // Mark order as completed if not already
        if ($order->status !== 'completed') {
            $order->markAsCompleted();
        }
    }

    /**
     * Generate license key for product.
     */
    public function generateLicenseKey(Order $order): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($order->product->name, 0, 3)));
        if (empty($prefix)) {
            $prefix = 'PRD';
        }
        $random = strtoupper(Str::random(12));
        $timestamp = now()->format('Ymd');
        // Use first 8 characters of UUID for order part
        $orderPart = strtoupper(substr(str_replace('-', '', $order->id), 0, 8));
        
        return "{$prefix}-{$timestamp}-{$orderPart}-{$random}";
    }
}
