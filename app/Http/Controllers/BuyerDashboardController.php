<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        // Get buyer-specific metrics
        $metrics = [
            'total_spent' => $user->transactionsAsBuyer()
                ->sum('amount') ?? 0,

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

        return view('dashboard.buyer', [
            'user' => $user,
            'metrics' => $metrics,
            'recentPurchases' => $recentPurchases,
            'referralStats' => $referralStats,
            'wishlisted' => $wishlisted,
        ]);
    }
}
