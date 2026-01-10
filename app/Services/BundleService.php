<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBundleItem;
use Illuminate\Support\Facades\DB;

class BundleService
{
    /**
     * Create a bundle with items.
     */
    public function createBundle(Product $bundle, array $items): Product
    {
        return DB::transaction(function () use ($bundle, $items) {
            $bundle->update(['is_bundle' => true]);

            $order = 0;
            foreach ($items as $item) {
                ProductBundleItem::create([
                    'bundle_id' => $bundle->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'order' => $order++,
                ]);
            }

            return $bundle->fresh(['bundleItems.product']);
        });
    }

    /**
     * Calculate bundle price.
     */
    public function calculateBundlePrice(Product $bundle): float
    {
        return $bundle->getBundlePrice();
    }

    /**
     * Validate bundle.
     */
    public function validateBundle(Product $bundle): bool
    {
        if (!$bundle->is_bundle) {
            return false;
        }

        if ($bundle->bundleItems()->count() === 0) {
            return false;
        }

        // Check if all bundle items are active
        foreach ($bundle->bundleItems as $item) {
            if (!$item->product->is_active) {
                return false;
            }
        }

        return true;
    }
}

