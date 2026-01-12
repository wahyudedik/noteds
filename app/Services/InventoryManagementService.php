<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductStockHistory;
use Illuminate\Support\Collection;
use App\Events\StockLowAlert;

class InventoryManagementService
{
    /**
     * Update stock and record history.
     */
    public function updateStock(
        Product $product,
        int $quantityChange,
        string $type,
        ?string $reason = null,
        ?Order $order = null,
        ?User $updatedBy = null
    ): ProductStockHistory {
        $quantityBefore = $product->stock ?? 0;
        $quantityAfter = $quantityBefore + $quantityChange;

        // Update product stock
        $product->update(['stock' => $quantityAfter]);

        // Record history
        $history = ProductStockHistory::create([
            'product_id' => $product->id,
            'change_type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reason' => $reason,
            'order_id' => $order?->id,
            'updated_by' => $updatedBy?->id,
        ]);

        // Check for low stock and send alert if needed
        if ($this->checkLowStock($product)) {
            $this->sendLowStockAlert($product);
        }

        return $history;
    }

    /**
     * Check if product is low stock.
     */
    public function checkLowStock(Product $product): bool
    {
        return $product->checkLowStock();
    }

    /**
     * Get all low stock products for seller.
     */
    public function checkLowStockForSeller(User $seller): Collection
    {
        return Product::where('user_id', $seller->id)
            ->lowStock()
            ->get();
    }

    /**
     * Send low stock alert.
     */
    public function sendLowStockAlert(Product $product): void
    {
        // Only send alert if not already sent recently
        if ($product->stock_alert_sent_at && $product->stock_alert_sent_at->gt(now()->subHours(config('seller.inventory.alert_cooldown_hours', 24)))) {
            return;
        }

        // Mark alert as sent
        $product->update(['stock_alert_sent_at' => now()]);

        // Fire event
        event(new StockLowAlert($product));
    }

    /**
     * Get stock history.
     */
    public function getStockHistory(Product $product, ?int $days = 30): Collection
    {
        return ProductStockHistory::forProduct($product->id)
            ->recent($days)
            ->with(['order', 'updatedBy'])
            ->get();
    }

    /**
     * Record sale stock change.
     */
    public function recordSale(Product $product, Order $order): ProductStockHistory
    {
        $quantity = $order->quantity ?? 1;
        
        return $this->updateStock(
            $product,
            -$quantity,
            'sale',
            'Order #' . $order->order_number,
            $order
        );
    }

    /**
     * Record restock.
     */
    public function recordRestock(
        Product $product,
        int $quantity,
        ?string $reason = null,
        ?User $updatedBy = null
    ): ProductStockHistory {
        return $this->updateStock(
            $product,
            $quantity,
            'restock',
            $reason ?? 'Manual restock',
            null,
            $updatedBy
        );
    }

    /**
     * Record manual adjustment.
     */
    public function recordAdjustment(
        Product $product,
        int $newQuantity,
        string $reason,
        ?User $updatedBy = null
    ): ProductStockHistory {
        $currentStock = $product->stock ?? 0;
        $quantityChange = $newQuantity - $currentStock;

        $product->update(['stock' => $newQuantity]);

        $history = ProductStockHistory::create([
            'product_id' => $product->id,
            'change_type' => 'adjustment',
            'quantity_change' => $quantityChange,
            'quantity_before' => $currentStock,
            'quantity_after' => $newQuantity,
            'reason' => $reason,
            'updated_by' => $updatedBy?->id,
        ]);

        // Check for low stock
        if ($this->checkLowStock($product)) {
            $this->sendLowStockAlert($product);
        }

        return $history;
    }
}

