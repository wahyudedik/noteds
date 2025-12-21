<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\SalesAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesAnalyticsController extends Controller
{
    public function __construct(
        private SalesAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'daily');
        $days = $request->get('days', 30);

        $stats = $this->analyticsService->getSalesStats($user, $days);
        $chartData = $this->analyticsService->getSalesChart($user, $period, $days);
        $topProducts = $this->analyticsService->getTopProducts($user, 10);
        $salesByCategory = $this->analyticsService->getSalesByCategory($user);

        return Inertia::render('Marketplace/Sales/Analytics', [
            'stats' => $stats,
            'chart_data' => $chartData,
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
            'period' => $period,
            'days' => $days,
        ]);
    }
}
