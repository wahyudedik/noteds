<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\SellerRating;
use App\Models\SellerPerformanceMetric;
use App\Models\ProductReviewReply;
use Illuminate\Support\Facades\DB;
use App\Events\SellerRatingUpdated;

class SellerRatingService
{
    /**
     * Calculate weighted composite rating.
     */
    public function calculateSellerRating(User $seller): float
    {
        $weights = $this->getRatingWeights();

        $reviewRating = $this->calculateReviewRating($seller);
        $fulfillmentRating = $this->calculateFulfillmentRating($seller);
        $responseTimeRating = $this->calculateResponseTimeRating($seller);

        $compositeRating = ($reviewRating * $weights['review'])
            + ($fulfillmentRating * $weights['fulfillment'])
            + ($responseTimeRating * $weights['response_time']);

        return round($compositeRating, 2);
    }

    /**
     * Update cached seller rating.
     */
    public function updateSellerRating(User $seller): void
    {
        $rating = $this->calculateSellerRating($seller);

        $seller->update(['seller_rating' => $rating]);

        event(new SellerRatingUpdated($seller, $rating));
    }

    /**
     * Create new seller rating.
     */
    public function createRating(User $seller, User $buyer, Order $order, array $data): SellerRating
    {
        // Calculate component ratings
        $reviewRating = $this->getReviewRatingFromOrder($order);
        $fulfillmentRating = $order->calculateFulfillmentRating();
        $responseTimeRating = $this->calculateResponseTimeRating($seller);

        $rating = SellerRating::create([
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'order_id' => $order->id,
            'rating' => $data['rating'] ?? ($reviewRating * 0.4 + $fulfillmentRating * 0.35 + $responseTimeRating * 0.25),
            'review_rating' => $reviewRating,
            'fulfillment_rating' => $fulfillmentRating,
            'response_rating' => $responseTimeRating,
            'comment' => $data['comment'] ?? null,
        ]);

        $this->updateSellerRating($seller);
        $this->recalculatePerformanceMetrics($seller);

        return $rating;
    }

    /**
     * Update existing rating.
     */
    public function updateRating(SellerRating $rating, array $data): SellerRating
    {
        $rating->update($data);

        if (isset($data['rating'])) {
            $rating->update(['rating' => $data['rating']]);
        }

        $this->updateSellerRating($rating->seller);
        $this->recalculatePerformanceMetrics($rating->seller);

        return $rating->fresh();
    }

    /**
     * Get detailed rating breakdown.
     */
    public function getRatingBreakdown(User $seller): array
    {
        $weights = $this->getRatingWeights();

        return [
            'overall_rating' => (float) ($seller->getSellerRating() ?? 0),
            'review_rating' => (float) $this->calculateReviewRating($seller),
            'fulfillment_rating' => (float) $this->calculateFulfillmentRating($seller),
            'response_time_rating' => (float) $this->calculateResponseTimeRating($seller),
            'weights' => $weights,
        ];
    }

    /**
     * Calculate fulfillment rating.
     */
    public function calculateFulfillmentRating(User $seller, ?int $days = 90): float
    {
        $periodDays = $days ?? config('seller.rating.fulfillment_period_days', 90);
        $startDate = now()->subDays($periodDays);

        $orders = Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalOrders = $orders->count();
        if ($totalOrders < config('seller.rating.min_orders_for_fulfillment_rating', 5)) {
            return 0.0;
        }

        $completedOrders = $orders->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->count();

        $fulfillmentRate = ($completedOrders / $totalOrders) * 5;

        return round($fulfillmentRate, 2);
    }

    /**
     * Calculate response time rating.
     */
    public function calculateResponseTimeRating(User $seller, ?int $days = 90): float
    {
        $periodDays = $days ?? config('seller.rating.response_time_period_days', 90);
        $startDate = now()->subDays($periodDays);
        $maxHours = config('seller.rating.max_response_time_hours', 120);

        $reviewReplies = DB::table('product_review_replies')
            ->join('product_reviews', 'product_review_replies.product_review_id', '=', 'product_reviews.id')
            ->join('products', 'product_reviews.product_id', '=', 'products.id')
            ->where('products.user_id', $seller->id)
            ->where('product_review_replies.created_at', '>=', $startDate)
            ->selectRaw('TIMESTAMPDIFF(HOUR, product_reviews.created_at, product_review_replies.created_at) as response_hours')
            ->get();

        if ($reviewReplies->isEmpty()) {
            return 0.0;
        }

        $averageHours = $reviewReplies->avg('response_hours');
        $rating = max(0, 5 - ($averageHours / 24));

        return round($rating, 2);
    }

    /**
     * Recalculate all seller metrics.
     */
    public function recalculatePerformanceMetrics(User $seller): void
    {
        $metrics = SellerPerformanceMetric::firstOrCreate(['seller_id' => $seller->id]);

        $periodDays = config('seller.rating.fulfillment_period_days', 90);
        $startDate = now()->subDays($periodDays);

        $orders = Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalOrders = $orders->count();
        $completedOrders = $orders->where('status', 'completed')->where('payment_status', 'paid')->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();
        $totalRevenue = $orders->where('status', 'completed')->where('payment_status', 'paid')->sum('total');
        $averageOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;
        $fulfillmentRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        $totalReviews = ProductReview::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })->count();

        $metrics->update([
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'fulfillment_rate' => $fulfillmentRate,
            'average_response_time_hours' => $this->getAverageResponseTimeHours($seller, $periodDays),
            'total_rating' => $this->calculateSellerRating($seller),
            'total_reviews' => $totalReviews,
            'last_calculated_at' => now(),
        ]);
    }

    /**
     * Get configurable rating weights.
     */
    public function getRatingWeights(): array
    {
        return config('seller.rating.weights', [
            'review' => 0.40,
            'fulfillment' => 0.35,
            'response_time' => 0.25,
        ]);
    }

    /**
     * Calculate review rating from product reviews.
     */
    protected function calculateReviewRating(User $seller): float
    {
        $reviews = ProductReview::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('is_verified_purchase', true)
        ->get();

        if ($reviews->isEmpty()) {
            return 0.0;
        }

        return (float) round($reviews->avg('rating'), 2);
    }

    /**
     * Get review rating from order's product review.
     */
    protected function getReviewRatingFromOrder(Order $order): float
    {
        $review = ProductReview::where('order_id', $order->id)->first();

        return $review ? (float) $review->rating : 0.0;
    }

    /**
     * Get average response time in hours.
     */
    protected function getAverageResponseTimeHours(User $seller, int $days): ?float
    {
        $startDate = now()->subDays($days);

        $responseTime = DB::table('product_review_replies')
            ->join('product_reviews', 'product_review_replies.product_review_id', '=', 'product_reviews.id')
            ->join('products', 'product_reviews.product_id', '=', 'products.id')
            ->where('products.user_id', $seller->id)
            ->where('product_review_replies.created_at', '>=', $startDate)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, product_reviews.created_at, product_review_replies.created_at)) as avg_hours')
            ->value('avg_hours');

        return $responseTime ? (float) $responseTime : null;
    }
}

