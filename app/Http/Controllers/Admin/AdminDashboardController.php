<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalSales = Order::where('payment_status', 'paid')->sum('total');
        $totalProducts = Product::count();

        $recentWithdrawals = Withdrawal::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pending_withdrawals' => $pendingWithdrawals,
                'total_users' => $totalUsers,
                'total_sales' => (float) $totalSales,
                'total_products' => $totalProducts,
            ],
            'recent_withdrawals' => $recentWithdrawals,
        ]);
    }
}
