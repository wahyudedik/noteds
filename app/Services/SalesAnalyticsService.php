<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsService
{
    /**
     * Get sales statistics for user.
     */
    public function getSalesStats(User $user, ?int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $orders = Order::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate)
        ->get();

        $totalSales = $orders->count();
        $totalRevenue = $orders->sum('total');
        $averageOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        return [
            'total_sales' => $totalSales,
            'total_revenue' => (float) $totalRevenue,
            'average_order_value' => (float) $averageOrderValue,
            'period_days' => $days,
        ];
    }

    /**
     * Get sales chart data.
     */
    public function getSalesChart(User $user, string $period = 'daily', int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $query = Order::whereHas('product', function ($q) use ($user) {
            $q->where('user_id', $user->id);
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
     * Get top products.
     */
    public function getTopProducts(User $user, int $limit = 10): array
    {
        return Product::where('user_id', $user->id)
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
                ];
            })
            ->toArray();
    }

    /**
     * Get sales by category.
     */
    public function getSalesByCategory(User $user): array
    {
        return Order::whereHas('product', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('payment_status', 'paid')
        ->join('products', 'orders.product_id', '=', 'products.id')
        ->select(
            'products.category',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(orders.total) as revenue')
        )
        ->groupBy('products.category')
        ->get()
            ->toArray();
    }

    /**
     * Get inventory metrics.
     */
    public function getInventoryMetrics(User $seller): array
    {
        $products = Product::where('user_id', $seller->id)->get();
        
        $totalProducts = $products->count();
        $totalStock = $products->sum('stock') ?? 0;
        $lowStockCount = $products->filter(fn($p) => $p->checkLowStock())->count();
        $outOfStockCount = $products->filter(fn($p) => $p->stock !== null && $p->stock <= 0)->count();
        
        // Calculate turnover rate (sales / average stock)
        $totalSales = Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', now()->subDays(30))
        ->sum('quantity') ?? 0;
        
        $averageStock = $totalProducts > 0 ? $totalStock / $totalProducts : 0;
        $turnoverRate = $averageStock > 0 ? ($totalSales / $averageStock) * 100 : 0;

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'turnover_rate' => (float) $turnoverRate,
        ];
    }

    /**
     * Get detailed product performance.
     */
    public function getProductPerformance(User $seller, ?int $days = 30): Collection
    {
        $startDate = now()->subDays($days ?? 30);

        return Product::where('user_id', $seller->id)
            ->withCount(['orders as total_sales' => function ($query) use ($startDate) {
                $query->where('payment_status', 'paid')
                    ->where('created_at', '>=', $startDate);
            }])
            ->withSum(['orders as total_revenue' => function ($query) use ($startDate) {
                $query->where('payment_status', 'paid')
                    ->where('created_at', '>=', $startDate);
            }], 'total')
            ->orderBy('total_sales', 'desc')
            ->get()
            ->map(function ($product) use ($startDate) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sales_count' => $product->total_sales ?? 0,
                    'revenue' => (float) ($product->total_revenue ?? 0),
                    'stock' => $product->stock,
                    'has_low_stock' => $product->checkLowStock(),
                    'average_rating' => $product->averageRating(),
                ];
            });
    }

    /**
     * Get revenue trends.
     */
    public function getRevenueTrends(User $seller, string $period = 'monthly', int $periods = 12): array
    {
        $startDate = now()->subMonths($periods);

        $query = Order::whereHas('product', function ($q) use ($seller) {
            $q->where('user_id', $seller->id);
        })
        ->where('payment_status', 'paid')
        ->where('created_at', '>=', $startDate);

        if ($period === 'monthly') {
            $data = $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        } elseif ($period === 'weekly') {
            $data = $query->select(
                DB::raw('YEARWEEK(created_at) as period'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        } else { // daily
            $data = $query->select(
                DB::raw('DATE(created_at) as period'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        }

        return $data->toArray();
    }
}

