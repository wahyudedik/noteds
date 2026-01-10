<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReviewReplyRequest;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ProductReviewReplyController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Store a newly created seller reply.
     */
    public function store(StoreProductReviewReplyRequest $request, ProductReview $productReview): RedirectResponse
    {
        // Verify seller owns the product
        if ($productReview->product->user_id !== auth()->id()) {
            abort(403, 'Only the product seller can reply to reviews.');
        }

        // Check if reply already exists
        if ($productReview->reply) {
            return back()->withErrors(['error' => 'A reply already exists for this review.']);
        }

        try {
            $this->reviewService->createSellerReply(
                $productReview,
                auth()->user(),
                $request->validated()['content']
            );

            return back()->with('success', 'Reply submitted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified reply.
     */
    public function update(StoreProductReviewReplyRequest $request, ProductReviewReply $reply): RedirectResponse
    {
        // Verify seller owns the reply
        if ($reply->seller_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $reply->update($request->validated());

            return back()->with('success', 'Reply updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified reply.
     */
    public function destroy(ProductReviewReply $reply): RedirectResponse
    {
        // Verify seller owns the reply
        if ($reply->seller_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $reply->delete(); // This will unlock the review via model boot method

            return back()->with('success', 'Reply deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
