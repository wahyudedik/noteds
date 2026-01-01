<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function __construct(
        private BalanceService $balanceService
    ) {}

    public function index()
    {
        $user = auth()->user();
        
        // Get balance
        $currentBalance = $this->balanceService->getBalance($user);
        
        // Get total earnings (sum of all sale transactions)
        $totalEarnings = Transaction::where('user_id', $user->id)
            ->where('type', 'sale')
            ->where('status', 'completed')
            ->sum('amount');

        // Get pending withdrawal amount
        $pendingWithdrawal = \App\Models\Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        // Get recent transactions
        $recentTransactions = $this->balanceService->getBalanceHistory($user, 10);

        // Get quick stats
        $totalSales = (int) Order::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('payment_status', 'paid')
        ->count();

        $productsSold = (int) Order::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('payment_status', 'paid')
        ->sum('quantity');

        $averageOrderValue = Order::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('payment_status', 'paid')
        ->avg('total');

        return Inertia::render('Marketplace/Wallet/Index', [
            'currentBalance' => (float) $currentBalance,
            'totalEarnings' => (float) $totalEarnings,
            'pendingWithdrawal' => (float) $pendingWithdrawal,
            'recentTransactions' => $recentTransactions,
            'totalSales' => $totalSales,
            'productsSold' => $productsSold,
            'averageOrderValue' => $averageOrderValue ? (float) round($averageOrderValue, 2) : 0.0,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();

        $query = Transaction::where('user_id', $user->id);

        // Apply filters
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Wallet/Transactions', [
            'transactions' => $transactions,
            'filters' => $request->only(['type', 'date_from', 'date_to']),
        ]);
    }

    public function sales(Request $request)
    {
        $user = auth()->user();

        $query = Order::whereHas('product', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['product', 'buyer']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Wallet/Sales', [
            'sales' => $sales,
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }
}

