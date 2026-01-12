<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SellerPerformanceMetric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SellerAnalyticsService
{
    /**
     * Get overall dashboard statistics.
     */
    public function getDashboardStats(User $seller, ?int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $orders = Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalSales = $orders->count();
        $totalRevenue = $orders->sum('total');
        $averageOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;
        $totalProducts = Product::where('user_id', $seller->id)->count();
        $sellerRating = $seller->getSellerRating() ?? 0;

        return [
            'total_sales' => $totalSales,
            'total_revenue' => (float) $totalRevenue,
            'average_order_value' => (float) $averageOrderValue,
            'total_products' => $totalProducts,
            'seller_rating' => (float) $sellerRating,
            'period_days' => $days,
        ];
    }

    /**
     * Get sales analytics with charts and trends.
     */
    public function getSalesAnalytics(User $seller, string $period = 'daily', int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $query = Order::whereHas('product', function ($q) use ($seller) {
            $q->where('user_id', $seller->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate);

        if ($period === 'daily') {
            $data = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        } elseif ($period === 'weekly') {
            $data = $query->select(
                DB::raw('YEARWEEK(created_at) as week'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();
        } else { // monthly
            $data = $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        }

        return $data->toArray();
    }

    /**
     * Get top/bottom performing products.
     */
    public function getProductPerformance(User $seller, int $limit = 10): Collection
    {
        return Product::where('user_id', $seller->id)
            ->withCount(['orders as total_sales' => function ($query) {
                $query->where('payment_status', 'paid');
            }])
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sales_count' => $product->total_sales,
                    'revenue' => (float) Order::where('product_id', $product->id)
                        ->where('payment_status', 'paid')
                        ->sum('total'),
                    'stock' => $product->stock,
                    'has_low_stock' => $product->checkLowStock(),
                ];
            });
    }

    /**
     * Get inventory status.
     */
    public function getInventoryStatus(User $seller): array
    {
        $products = Product::where('user_id', $seller->id)->get();
        $totalProducts = $products->count();
        $lowStockProducts = $products->filter(fn($p) => $p->checkLowStock());
        $outOfStockProducts = $products->filter(fn($p) => $p->stock !== null && $p->stock <= 0);
        $totalStockValue = $products->sum(fn($p) => ($p->stock ?? 0) * (float) ($p->price ?? 0));

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockProducts->count(),
            'out_of_stock_count' => $outOfStockProducts->count(),
            'total_stock_value' => (float) $totalStockValue,
            'low_stock_products' => $lowStockProducts->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => $p->stock,
                'threshold' => $p->low_stock_threshold ?? $seller->low_stock_alert_threshold ?? config('seller.inventory.default_low_stock_threshold', 10),
            ])->values(),
        ];
    }

    /**
     * Get rating analytics.
     */
    public function getRatingAnalytics(User $seller, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $reviews = ProductReview::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalReviews = $reviews->count();
        $averageRating = $reviews->avg('rating') ?? 0;
        $ratingDistribution = $reviews->groupBy('rating')->map->count();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => (float) $averageRating,
            'rating_distribution' => $ratingDistribution->toArray(),
            'seller_rating' => (float) ($seller->getSellerRating() ?? 0),
        ];
    }

    /**
     * Get revenue breakdown.
     */
    public function getRevenueBreakdown(User $seller, string $period = 'monthly'): array
    {
        $startDate = now()->subMonths(12);

        $query = Order::whereHas('product', function ($q) use ($seller) {
            $q->where('user_id', $seller->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate);

        if ($period === 'monthly') {
            $data = $query->join('products', 'orders.product_id', '=', 'products.id')
                ->select(
                    'products.category',
                    DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m") as period'),
                    DB::raw('SUM(orders.total) as revenue'),
                    DB::raw('COUNT(*) as orders_count')
                )
                ->groupBy('products.category', 'period')
                ->orderBy('period')
                ->get();
        } else {
            $data = $query->join('products', 'orders.product_id', '=', 'products.id')
                ->select(
                    'products.category',
                    DB::raw('SUM(orders.total) as revenue'),
                    DB::raw('COUNT(*) as orders_count')
                )
                ->groupBy('products.category')
                ->get();
        }

        return $data->toArray();
    }

    /**
     * Get order fulfillment metrics.
     */
    public function getOrderFulfillmentMetrics(User $seller, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $orders = Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalOrders = $orders->count();
        $completedOrders = $orders->where('status', 'completed')->where('payment_status', 'paid')->count();
        $fulfillmentRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'fulfillment_rate' => (float) $fulfillmentRate,
        ];
    }

    /**
     * Get response time metrics.
     */
    public function getResponseTimeMetrics(User $seller, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // Get average response time from review replies
        $reviewReplies = DB::table('product_review_replies')
            ->join('product_reviews', 'product_review_replies.product_review_id', '=', 'product_reviews.id')
            ->join('products', 'product_reviews.product_id', '=', 'products.id')
            ->where('products.user_id', $seller->id)
            ->where('product_review_replies.created_at', '>=', $startDate)
            ->selectRaw('TIMESTAMPDIFF(HOUR, product_reviews.created_at, product_review_replies.created_at) as response_hours')
            ->get();

        $averageResponseTimeHours = $reviewReplies->count() > 0 
            ? $reviewReplies->avg('response_hours') 
            : null;

        return [
            'average_response_time_hours' => $averageResponseTimeHours !== null ? (float) $averageResponseTimeHours : null,
            'total_responses' => $reviewReplies->count(),
        ];
    }
}

