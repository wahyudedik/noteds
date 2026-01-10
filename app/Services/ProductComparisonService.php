<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class ProductComparisonService
{
    const MAX_COMPARISON_ITEMS = 4;

    /**
     * Add product to comparison.
     */
    public function addToComparison(string $sessionId, Product $product): void
    {
        $comparisonIds = Session::get("comparison_{$sessionId}", []);

        // Check if already in comparison
        if (in_array($product->id, $comparisonIds)) {
            return;
        }

        // Check max items
        if (count($comparisonIds) >= self::MAX_COMPARISON_ITEMS) {
            throw new \Exception('Maximum ' . self::MAX_COMPARISON_ITEMS . ' products can be compared');
        }

        $comparisonIds[] = $product->id;
        Session::put("comparison_{$sessionId}", $comparisonIds);
    }

    /**
     * Remove product from comparison.
     */
    public function removeFromComparison(string $sessionId, Product $product): void
    {
        $comparisonIds = Session::get("comparison_{$sessionId}", []);
        $comparisonIds = array_values(array_filter($comparisonIds, function ($id) use ($product) {
            return $id !== $product->id;
        }));
        Session::put("comparison_{$sessionId}", $comparisonIds);
    }

    /**
     * Get comparison list.
     */
    public function getComparison(string $sessionId): Collection
    {
        // Use session for both guests and authenticated users
        $comparisonIds = Session::get("comparison_{$sessionId}", []);

        if (empty($comparisonIds)) {
            return collect([]);
        }

        return Product::whereIn('id', $comparisonIds)
            ->with(['seller', 'reviews'])
            ->get();
    }

    /**
     * Compare products and return comparison data.
     */
    public function compareProducts(array $productIds): array
    {
        $products = Product::whereIn('id', $productIds)
            ->with(['seller', 'reviews'])
            ->get();

        if ($products->count() === 0) {
            return [];
        }

        $comparison = [
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'stock' => $product->stock,
                    'rating' => $product->averageRating(),
                    'reviews_count' => $product->reviews()->count(),
                    'sales_count' => $product->sales_count,
                    'seller' => $product->seller->name ?? null,
                    'image' => $product->image_url,
                    'description' => $product->description,
                ];
            }),
            'attributes' => [
                'Price',
                'Category',
                'Stock',
                'Rating',
                'Reviews',
                'Sales',
                'Seller',
            ],
        ];

        return $comparison;
    }

    /**
     * Save comparison.
     */
    protected function saveComparison(string $sessionId, Collection $comparison): void
    {
        $ids = $comparison->pluck('id')->toArray();
        Session::put("comparison_{$sessionId}", $ids);
    }
}

