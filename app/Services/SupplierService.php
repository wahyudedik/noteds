<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Collection;

class SupplierService
{
    /**
     * Register supplier.
     *
     * @param User $user
     * @param array $data
     * @return Supplier
     */
    public function registerSupplier(User $user, array $data): Supplier
    {
        return Supplier::create([
            'user_id' => $user->id,
            'supplier_name' => $data['supplier_name'],
            'supplier_category' => $data['supplier_category'],
            'description' => $data['description'],
            'location' => $data['location'] ?? null,
            'contact_info' => $data['contact_info'],
            'specialties' => $data['specialties'] ?? null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'delivery_scope' => $data['delivery_scope'] ?? null,
            'is_active' => true,
            'is_verified' => false, // Requires admin verification
        ]);
    }

    /**
     * Update supplier info.
     *
     * @param Supplier $supplier
     * @param array $data
     * @return Supplier
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update([
            'supplier_name' => $data['supplier_name'] ?? $supplier->supplier_name,
            'supplier_category' => $data['supplier_category'] ?? $supplier->supplier_category,
            'description' => $data['description'] ?? $supplier->description,
            'location' => $data['location'] ?? $supplier->location,
            'contact_info' => $data['contact_info'] ?? $supplier->contact_info,
            'specialties' => $data['specialties'] ?? $supplier->specialties,
            'min_order_amount' => $data['min_order_amount'] ?? $supplier->min_order_amount,
            'delivery_scope' => $data['delivery_scope'] ?? $supplier->delivery_scope,
        ]);

        return $supplier->fresh();
    }

    /**
     * Get supplier details dengan reviews, products, stats.
     *
     * @param Supplier $supplier
     * @return array
     */
    public function getSupplierDetails(Supplier $supplier): array
    {
        $supplier->load(['seller', 'reviews.user', 'products']);

        // Increment view count
        $supplier->increment('view_count');

        return [
            'supplier' => $supplier,
            'reviews' => $supplier->reviews()->with('user')->latest()->paginate(10),
            'products' => $supplier->products()->active()->latest()->get(),
            'stats' => [
                'total_reviews' => $supplier->review_count,
                'average_rating' => $supplier->rating,
                'total_orders' => $supplier->order_count,
                'total_views' => $supplier->view_count,
            ],
        ];
    }

    /**
     * Search suppliers by filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function searchSuppliers(array $filters): Collection
    {
        $query = Supplier::query()->with('seller');

        if (isset($filters['category'])) {
            $query->where('supplier_category', $filters['category']);
        }

        if (isset($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        if (isset($filters['verified_only']) && $filters['verified_only']) {
            $query->where('is_verified', true);
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->where('is_active', true);
        }

        // Default sorting
        $sortBy = $filters['sort_by'] ?? 'rating';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if ($sortBy === 'rating') {
            $query->orderBy('rating', $sortOrder)
                ->orderBy('review_count', 'desc');
        } elseif ($sortBy === 'reviews') {
            $query->orderBy('review_count', $sortOrder);
        } elseif ($sortBy === 'orders') {
            $query->orderBy('order_count', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        return $query->get();
    }
}

