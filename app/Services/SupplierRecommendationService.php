<?php

namespace App\Services;

use App\Models\BusinessSupplierMapping;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Support\Collection;

class SupplierRecommendationService
{
    /**
     * Get recommended suppliers untuk business type tertentu.
     *
     * @param string $businessType
     * @param string|null $location
     * @param int $limit
     * @return Collection
     */
    public function getRecommendedSuppliers(string $businessType, ?string $location = null, int $limit = 5): Collection
    {
        // Get supplier categories yang dibutuhkan untuk business type ini
        $mappings = BusinessSupplierMapping::where('business_type', $businessType)
            ->where('is_active', true)
            ->orderBy('priority_order')
            ->get();

        $recommendations = collect();

        foreach ($mappings as $map) {
            // Get registered suppliers
            $suppliers = Supplier::where('supplier_category', $map->supplier_category)
                ->where('is_active', true)
                ->when($location, function ($q) use ($location) {
                    $q->where('location', 'like', "%{$location}%");
                })
                ->orderBy('rating', 'desc')
                ->orderBy('order_count', 'desc')
                ->orderBy('review_count', 'desc')
                ->limit($limit)
                ->get();

            // Also get products from existing sellers that match the category
            $products = Product::where('category', $map->supplier_category)
                ->where('is_active', true)
                ->with(['seller'])
                ->has('seller')
                ->orderBy('sales_count', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit($limit)
                ->get();

            // Combine suppliers and products (grouped by seller)
            $combinedSuppliers = collect();

            // Add registered suppliers
            foreach ($suppliers as $supplier) {
                $combinedSuppliers->push([
                    'type' => 'registered',
                    'id' => $supplier->id,
                    'name' => $supplier->supplier_name,
                    'description' => $supplier->description,
                    'location' => $supplier->location,
                    'rating' => $supplier->rating,
                    'review_count' => $supplier->review_count,
                    'specialties' => $supplier->specialties ?? [],
                    'min_order' => $supplier->min_order_amount,
                    'seller_profile' => [
                        'id' => $supplier->seller->id,
                        'name' => $supplier->seller->name,
                        'business_name' => $supplier->seller->business_name,
                    ],
                    'contact_info' => $supplier->contact_info,
                ]);
            }

            // Add products from sellers (as suppliers)
            foreach ($products as $product) {
                if (!$product->seller) {
                    continue;
                }
                
                // Check if seller already added as registered supplier
                $exists = $combinedSuppliers->contains(function ($item) use ($product) {
                    return isset($item['seller_profile']['id']) && $item['seller_profile']['id'] === $product->seller->id;
                });

                if (!$exists) {
                    $combinedSuppliers->push([
                        'type' => 'product_based',
                        'id' => $product->id,
                        'name' => $product->seller->business_name ?? $product->seller->name,
                        'description' => $product->description,
                        'location' => null, // Adjust based on your user model
                        'rating' => $product->averageRating() ?? 0,
                        'review_count' => $product->reviews()->count(),
                        'specialties' => [],
                        'min_order' => null,
                        'seller_profile' => [
                            'id' => $product->seller->id,
                            'name' => $product->seller->name,
                            'business_name' => $product->seller->business_name,
                        ],
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                    ]);
                }
            }

            // Sort combined by rating and review count
            $combinedSuppliers = $combinedSuppliers->sortByDesc(function ($item) {
                return ($item['rating'] * 0.7) + ($item['review_count'] * 0.3);
            })->take($limit);

            $recommendations->push([
                'category' => $map->supplier_category,
                'category_label' => $map->category_label,
                'note' => $map->recommendation_note,
                'suppliers' => $combinedSuppliers->values(),
            ]);
        }

        return $recommendations;
    }

    /**
     * Detect business type dari post content.
     *
     * @param Post $post
     * @return string|null
     */
    public function detectBusinessTypeFromPost(Post $post): ?string
    {
        $content = strtolower($post->title . ' ' . $post->content);

        // Get all active business categories with keywords
        $categories = \App\Models\BusinessSupplierCategory::where('is_active', true)
            ->get();

        $matches = [];

        foreach ($categories as $category) {
            if ($category->keywords && is_array($category->keywords)) {
                foreach ($category->keywords as $keyword) {
                    if (str_contains($content, strtolower($keyword))) {
                        $matches[$category->business_type] = ($matches[$category->business_type] ?? 0) + 1;
                    }
                }
            }
        }

        if (empty($matches)) {
            return null;
        }

        // Return business type with most keyword matches
        arsort($matches);
        return array_key_first($matches);
    }

    /**
     * Get semua active business types.
     *
     * @return Collection
     */
    public function getBusinessTypes(): Collection
    {
        return \App\Models\BusinessSupplierCategory::where('is_active', true)
            ->orderBy('business_name')
            ->get()
            ->map(function ($category) {
                return [
                    'value' => $category->business_type,
                    'label' => $category->business_name,
                    'description' => $category->description,
                ];
            });
    }

    /**
     * Get supplier categories yang dibutuhkan untuk business type.
     *
     * @param string $businessType
     * @return Collection
     */
    public function getSupplierCategoriesForBusiness(string $businessType): Collection
    {
        return BusinessSupplierMapping::where('business_type', $businessType)
            ->where('is_active', true)
            ->orderBy('priority_order')
            ->get()
            ->map(function ($mapping) {
                return [
                    'category' => $mapping->supplier_category,
                    'label' => $mapping->category_label,
                    'note' => $mapping->recommendation_note,
                    'priority' => $mapping->priority_order,
                ];
            });
    }
}

