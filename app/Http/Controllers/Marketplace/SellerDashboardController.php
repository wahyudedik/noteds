<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\SellerAnalyticsService;
use App\Services\SalesAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerDashboardController extends Controller
{
    public function __construct(
        private SellerAnalyticsService $analyticsService,
        private SalesAnalyticsService $salesAnalyticsService
    ) {}

    /**
     * Display the main dashboard.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user();
        $days = $request->get('days', 30);

        $stats = $this->analyticsService->getDashboardStats($seller, $days);
        $inventoryStatus = $this->analyticsService->getInventoryStatus($seller);
        $recentOrders = \App\Models\Order::whereHas('product', function ($query) use ($seller) {
            $query->where('user_id', $seller->id);
        })
        ->with(['product', 'buyer'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        return Inertia::render('Marketplace/Seller/Dashboard/Index', [
            'stats' => $stats,
            'inventory_status' => $inventoryStatus,
            'recent_orders' => $recentOrders,
            'days' => $days,
        ]);
    }

    /**
     * Display sales analytics page.
     */
    public function sales(Request $request): Response
    {
        $seller = $request->user();
        $period = $request->get('period', 'daily');
        $days = $request->get('days', 30);

        $stats = $this->salesAnalyticsService->getSalesStats($seller, $days);
        $chartData = $this->analyticsService->getSalesAnalytics($seller, $period, $days);
        $topProducts = $this->analyticsService->getProductPerformance($seller, 10);
        $revenueBreakdown = $this->analyticsService->getRevenueBreakdown($seller, $period);

        return Inertia::render('Marketplace/Seller/Dashboard/Sales', [
            'stats' => $stats,
            'chart_data' => $chartData,
            'top_products' => $topProducts,
            'revenue_breakdown' => $revenueBreakdown,
            'period' => $period,
            'days' => $days,
        ]);
    }

    /**
     * Display inventory management page.
     */
    public function inventory(Request $request): Response
    {
        $seller = $request->user();

        $inventoryStatus = $this->analyticsService->getInventoryStatus($seller);

        return Inertia::render('Marketplace/Seller/Dashboard/Inventory', [
            'inventory_status' => $inventoryStatus,
        ]);
    }

    /**
     * Display performance metrics page.
     */
    public function performance(Request $request): Response
    {
        $seller = $request->user();
        $days = $request->get('days', 30);

        $ratingAnalytics = $this->analyticsService->getRatingAnalytics($seller, $days);
        $fulfillmentMetrics = $this->analyticsService->getOrderFulfillmentMetrics($seller, $days);
        $responseTimeMetrics = $this->analyticsService->getResponseTimeMetrics($seller, $days);

        return Inertia::render('Marketplace/Seller/Dashboard/Performance', [
            'rating_analytics' => $ratingAnalytics,
            'fulfillment_metrics' => $fulfillmentMetrics,
            'response_time_metrics' => $responseTimeMetrics,
            'days' => $days,
        ]);
    }
}
