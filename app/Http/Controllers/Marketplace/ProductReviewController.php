<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasPurchased) {
            return back()->withErrors(['error' => 'You must purchase this product before leaving a review']);
        }

        // Check if user already has a review for this product
        $existingReview = ProductReview::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return back()->withErrors(['error' => 'You have already reviewed this product. You can edit your existing review.']);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        ProductReview::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Review submitted successfully');
    }

    public function update(Request $request, ProductReview $productReview)
    {
        // Verify ownership
        if ($productReview->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $productReview->update($validated);

        return back()->with('success', 'Review updated successfully');
    }

    public function destroy(ProductReview $productReview)
    {
        // Verify ownership
        if ($productReview->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $productReview->delete();

        return back()->with('success', 'Review deleted successfully');
    }
}

