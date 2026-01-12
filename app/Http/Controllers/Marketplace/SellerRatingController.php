<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\SellerRating;
use App\Services\SellerRatingService;
use App\Http\Requests\CreateSellerRatingRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerRatingController extends Controller
{
    public function __construct(
        private SellerRatingService $ratingService
    ) {}

    /**
     * Show seller rating and reviews.
     */
    public function show(User $seller, Request $request): Response
    {
        $ratingBreakdown = $this->ratingService->getRatingBreakdown($seller);
        
        $ratings = SellerRating::forSeller($seller->id)
            ->with(['buyer', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Seller/Rating/Show', [
            'seller' => $seller,
            'rating_breakdown' => $ratingBreakdown,
            'ratings' => $ratings,
        ]);
    }

    /**
     * Show rating form (for buyer).
     */
    public function create(Order $order, Request $request): Response
    {
        // Authorize: buyer must own the order
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Check if order is completed
        if ($order->status !== 'completed' || $order->payment_status !== 'paid') {
            abort(403, 'Can only rate sellers for completed orders');
        }

        // Check if already rated
        $existingRating = SellerRating::where('order_id', $order->id)
            ->where('buyer_id', $request->user()->id)
            ->first();

        if ($existingRating) {
            return redirect()
                ->route('marketplace.sellers.rating', $order->product->seller)
                ->with('info', 'You have already rated this seller for this order');
        }

        $order->load(['product', 'product.seller']);

        return Inertia::render('Marketplace/Seller/Rating/Create', [
            'order' => $order,
            'seller' => $order->product->seller,
        ]);
    }

    /**
     * Submit seller rating.
     */
    public function store(CreateSellerRatingRequest $request, Order $order)
    {
        // Authorize: buyer must own the order
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        // Check if order is completed
        if ($order->status !== 'completed' || $order->payment_status !== 'paid') {
            abort(403, 'Can only rate sellers for completed orders');
        }

        // Check if already rated
        $existingRating = SellerRating::where('order_id', $order->id)
            ->where('buyer_id', $request->user()->id)
            ->first();

        if ($existingRating) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You have already rated this seller for this order',
                ], 422);
            }

            return back()->withErrors(['rating' => 'You have already rated this seller for this order']);
        }

        $seller = $order->product->seller;
        $buyer = $request->user();
        $validated = $request->validated();

        $rating = $this->ratingService->createRating($seller, $buyer, $order, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rating submitted successfully',
                'rating' => $rating->load(['seller', 'buyer', 'order']),
            ], 201);
        }

        return redirect()
            ->route('marketplace.sellers.rating', $seller)
            ->with('success', 'Rating submitted successfully');
    }

    /**
     * Update rating (if allowed).
     */
    public function update(CreateSellerRatingRequest $request, SellerRating $rating)
    {
        // Authorize: buyer must own the rating
        if ($rating->buyer_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();
        $rating = $this->ratingService->updateRating($rating, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rating updated successfully',
                'rating' => $rating->load(['seller', 'buyer', 'order']),
            ]);
        }

        return back()->with('success', 'Rating updated successfully');
    }

    /**
     * Get detailed rating breakdown (API).
     */
    public function getRatingDetails(User $seller, Request $request)
    {
        $breakdown = $this->ratingService->getRatingBreakdown($seller);

        return response()->json($breakdown);
    }
}
