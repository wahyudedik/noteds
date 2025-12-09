<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralTransaction;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    /**
     * Display the user's referral dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Generate referral code if not exists
        $referralCode = $user->referral_code ?? $user->generateReferralCode();
        $referralLink = route('register', ['ref' => $referralCode]);

        // Get referral stats
        $totalReferrals = $user->referralsMade()->count();
        $totalEarned = $user->getTotalReferralRewards();
        $pendingRewards = $user->getPendingReferralRewards();

        $signupRewards = $user->referralsMade()->byType('signup')->paid()->count();
        $transactionRewards = $user->referralsMade()->byType('transaction')->paid()->count();

        // Recent referrals
        $recentReferrals = $user->referralsMade()
            ->with('referred')
            ->latest()
            ->limit(10)
            ->get();

        // Get reward config
        $rewardConfig = $this->referralService->getRewardConfig();

        return view('referral.index', compact(
            'user',
            'referralCode',
            'referralLink',
            'totalReferrals',
            'totalEarned',
            'pendingRewards',
            'signupRewards',
            'transactionRewards',
            'recentReferrals',
            'rewardConfig'
        ));
    }

    /**
     * Display referral statistics.
     */
    public function statistics(): View
    {
        $user = auth()->user();

        // Get all referrals with detailed info
        $referrals = $user->referralsMade()
            ->with('referred')
            ->latest()
            ->paginate(20);

        return view('referral.statistics', compact('referrals'));
    }

    /**
     * Display referral commission transaction history.
     */
    public function transactions(Request $request): View
    {
        $user = auth()->user();
        $status = $request->get('status', 'all');

        // Get all transaction commissions received by this user
        $query = ReferralTransaction::where('user_id', $user->id)
            ->with('referral', 'admin')
            ->latest();

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transactions = $query->paginate(20);

        // Calculate summary
        $totalReceived = ReferralTransaction::where('user_id', $user->id)
            ->where('status', 'sent')
            ->sum('amount');

        $pendingAmount = ReferralTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        return view('referral.transaction-history', compact(
            'user',
            'transactions',
            'totalReceived',
            'pendingAmount',
            'status'
        ));
    }
}
