<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AffiliateLeaderboardController extends Controller
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Display affiliate leaderboard.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'all'); // all, 7d, 30d, 90d
        $sortBy = $request->input('sort', 'revenue'); // revenue, conversions, commissions

        $since = match($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => null,
        };

        // Get all affiliates with stats
        $affiliates = User::whereHas('affiliateLinks')
            ->with(['affiliateLinks', 'affiliateCommissions', 'affiliateConversions'])
            ->get()
            ->map(function ($user) use ($since, $sortBy) {
                $stats = $this->affiliateService->getAffiliateStats($user);
                
                // Filter by period if needed
                $conversionsQuery = $user->affiliateConversions();
                $commissionsQuery = $user->affiliateCommissions();
                
                if ($since) {
                    $conversionsQuery->where('created_at', '>=', $since);
                    $commissionsQuery->where('created_at', '>=', $since);
                }
                
                $periodConversions = $conversionsQuery->count();
                $periodCommissions = $commissionsQuery->sum('commission_amount');
                $periodRevenue = $conversionsQuery->sum('transaction_amount');
                
                return [
                    'user' => $user,
                    'total_revenue' => $periodRevenue,
                    'total_conversions' => $periodConversions,
                    'total_commissions' => $periodCommissions,
                    'conversion_rate' => $stats['conversion_rate'],
                    'total_clicks' => $stats['total_clicks'],
                ];
            })
            ->filter(function ($data) {
                // Only show affiliates with at least 1 conversion
                return $data['total_conversions'] > 0;
            })
            ->sortByDesc(function ($data) use ($sortBy) {
                return match($sortBy) {
                    'conversions' => $data['total_conversions'],
                    'commissions' => $data['total_commissions'],
                    default => $data['total_revenue'],
                };
            })
            ->values()
            ->map(function ($data, $index) {
                $data['rank'] = $index + 1;
                return $data;
            })
            ->take(100); // Top 100

        return view('affiliate.leaderboard', [
            'affiliates' => $affiliates,
            'period' => $period,
            'sortBy' => $sortBy,
        ]);
    }
}
