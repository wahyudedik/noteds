<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Models\ProductReview;
use App\Models\Order;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Display a listing of reviews for a product.
     */
    public function index(Request $request, Product $product): Response
    {
        $sortBy = $request->get('sort', 'recent'); // helpful, recent, rating

        $query = $product->reviews()
            ->with(['user', 'media', 'reply.seller'])
            ->active();

        $reviews = $this->reviewService->sortReviews($query, $sortBy)
            ->paginate(10);

        return Inertia::render('Marketplace/ProductReviews', [
            'product' => $product,
            'reviews' => $reviews,
            'sortBy' => $sortBy,
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show(ProductReview $productReview): Response
    {
        $productReview->load(['user', 'product', 'media', 'votes', 'reply.seller']);

        return Inertia::render('Marketplace/ProductReviewShow', [
            'review' => $productReview,
        ]);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasPurchased) {
            return back()->withErrors(['error' => 'You must purchase this product before leaving a review']);
        }

        // Check if user already has a verified review for this order (if order_id provided)
        if ($request->order_id) {
            $existingReview = ProductReview::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('order_id', $request->order_id)
                ->where('is_verified_purchase', true)
                ->first();

            if ($existingReview) {
                return back()->withErrors(['error' => 'You have already reviewed this product for this order.']);
            }
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['product_id'] = $product->id;

        $mediaFiles = $request->file('media', []);

        try {
            $review = $this->reviewService->createReview($data, $mediaFiles);

            return back()->with('success', 'Review submitted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified review.
     */
    public function update(UpdateProductReviewRequest $request, ProductReview $productReview): RedirectResponse
    {
        $data = $request->validated();
        $mediaFiles = $request->file('media', []);

        try {
            $this->reviewService->updateReview($productReview, $data, $mediaFiles);

            return back()->with('success', 'Review updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(ProductReview $productReview): RedirectResponse
    {
        // Verify ownership
        if ($productReview->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->reviewService->deleteReview($productReview);

            return back()->with('success', 'Review deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Vote helpful/not helpful on a review.
     */
    public function voteHelpful(Request $request, ProductReview $productReview): RedirectResponse
    {
        $request->validate([
            'helpful' => ['nullable', 'boolean'],
        ]);

        $helpful = $request->boolean('helpful', true);

        try {
            $this->reviewService->voteHelpful($productReview, auth()->user(), $helpful);

            return back()->with('success', $helpful ? 'Marked as helpful' : 'Marked as not helpful');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove vote from a review.
     */
    public function removeVote(ProductReview $productReview): RedirectResponse
    {
        try {
            $this->reviewService->removeVote($productReview, auth()->user());

            return back()->with('success', 'Vote removed');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload media to an existing review.
     */
    public function uploadMedia(Request $request, ProductReview $productReview): RedirectResponse
    {
        // Verify ownership
        if ($productReview->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if review is locked
        if ($productReview->isLocked()) {
            return back()->withErrors(['error' => 'Review is locked and cannot be edited']);
        }

        $request->validate([
            'media' => ['required', 'array', 'max:5'],
            'media.*' => ['file', 'mimes:jpeg,jpg,png,gif,mp4,mov,avi', 'max:5120'],
        ]);

        // Check total media count
        $currentMediaCount = $productReview->media()->count();
        $newMediaCount = count($request->file('media', []));

        if ($currentMediaCount + $newMediaCount > 5) {
            return back()->withErrors(['error' => 'Maximum 5 media files allowed per review']);
        }

        try {
            $mediaFiles = $request->file('media', []);
            $this->reviewService->uploadMedia($productReview, $mediaFiles);

            return back()->with('success', 'Media uploaded successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
