<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class SellerDashboardController extends Controller
{
    /**
     * Display the seller dashboard.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Get currency service
        $currencyService = app(\App\Services\CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($user);
        $baseCurrency = $currencyService->getBaseCurrency();

        // Get seller-specific metrics
        $sellerTransactions = $user->transactionsAsSeller();

        $totalRevenueBase = $sellerTransactions
            ->sum('amount') ?? 0;

        $metrics = [
            'total_revenue' => $totalRevenueBase,
            'total_revenue_display' => currency($totalRevenueBase, $userCurrency, $baseCurrency),

            'notes_published' => $user->notes()
                ->where('status', 'published')
                ->count() ?? 0,

            'total_sales' => $sellerTransactions
                ->count() ?? 0,

            'average_rating' => 0, // Can be calculated from ratings table if exists
        ];

        // Get best performing notes (by revenue)
        $bestPerforming = $user->notes()
            ->where('status', 'published')
            ->with(['transactionsAsSeller' => fn($q) => $q->where('seller_id', $user->id)])
            ->get()
            ->sortByDesc(
                fn($note) =>
                $note->transactionsAsSeller->sum('amount')
            )
            ->take(5)
            ->values();

        // Get referral data for seller (affiliate/share program)
        $affiliateStats = [
            'affiliate_code' => $user->affiliate_code ?? null,
            'affiliate_referrals' => $user->referralsMade()
                ->count() ?? 0,
            'affiliate_earnings' => $user->affiliate_earnings ?? 0,
        ];

        // Get recent sales
        $recentSales = $sellerTransactions
            ->whereNotNull('note_id')  // Only include transactions with notes
            ->with(['note', 'buyer'])
            ->latest('created_at')
            ->take(10)
            ->get();

        // Get sales trend (last 30 days)
        $salesTrend = DB::table('transactions')
            ->where('seller_id', $user->id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->get();

        return view('dashboard.seller', [
            'user' => $user,
            'metrics' => $metrics,
            'bestPerforming' => $bestPerforming,
            'affiliateStats' => $affiliateStats,
            'recentSales' => $recentSales,
            'salesTrend' => $salesTrend,
            'userCurrency' => $userCurrency,
            'baseCurrency' => $baseCurrency,
        ]);
    }
}
