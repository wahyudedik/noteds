<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ContentRecommendationEngine;
use App\Services\GrowthHackingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class BuyerDashboardController extends Controller
{
    /**
     * Display the buyer dashboard.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Get currency service
        $currencyService = app(\App\Services\CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($user);
        $baseCurrency = $currencyService->getBaseCurrency();

        // Get buyer-specific metrics
        $totalSpentBase = $user->transactionsAsBuyer()
            ->sum('amount') ?? 0;

        $metrics = [
            'total_spent' => $totalSpentBase,
            'total_spent_display' => currency($totalSpentBase, $userCurrency, $baseCurrency),

            'notes_purchased' => $user->purchasedNotes()
                ->count() ?? 0,

            'collections_count' => $user->collections()
                ->count() ?? 0,

            'total_ratings' => 0, // Can be calculated from ratings table if exists
        ];

        // Get recent purchased notes
        $recentPurchases = $user->purchasedNotes()
            ->with(['note' => fn($q) => $q->with('seller')])
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(fn($pn) => $pn->note)
            ->filter();

        // Get referral data for buyer
        $referralStats = [
            'referral_code' => $user->referral_code ?? null,
            'referrals_count' => $user->referralsMade()
                ->count() ?? 0,
            'referral_earnings' => $user->referral_earnings ?? 0,
        ];

        // Get wishlisted notes (collections)
        $wishlisted = collect();

        // Get personalized recommendations
        $engine = app(ContentRecommendationEngine::class);
        $recommendations = $engine->getPersonalizedRecommendations($user, 8);

        // Get growth hacking data
        $growthService = app(GrowthHackingService::class);
        $streakInfo = $growthService->updateStreak($user);

        return view('dashboard.buyer', [
            'user' => $user,
            'metrics' => $metrics,
            'recentPurchases' => $recentPurchases,
            'referralStats' => $referralStats,
            'wishlisted' => $wishlisted,
            'userCurrency' => $userCurrency,
            'baseCurrency' => $baseCurrency,
            'recommendations' => $recommendations,
            'streakInfo' => $streakInfo,
        ]);
    }
}
