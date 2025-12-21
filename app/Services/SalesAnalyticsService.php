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
}

