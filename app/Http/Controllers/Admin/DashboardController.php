<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Referral;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdraw;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_notes' => Note::count(),
            'public_notes' => Note::where('is_public', true)->count(),
            'total_transactions' => Transaction::count(),
            'total_revenue' => Transaction::where('status', 'success')->sum('commission'),
            'pending_withdraws' => Withdraw::where('status', 'pending')->count(),
            'total_withdraws' => Withdraw::count(),
            'pending_subscriptions' => Subscription::where('status', 'pending')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
        ];

        // Get platform wallet balance (admin wallet)
        $admin = User::where('role', 'admin')->first();
        $platformBalance = $admin ? ($admin->wallet?->balance ?? 0) : 0;

        // Wallet Analytics
        // Note: Topups might be tracked differently, for now we calculate from wallet balance changes
        // Total successful transactions can be used as proxy for wallet activity
        $walletStats = [
            'total_wallet_balance' => User::sum('wallet_balance'),
            'avg_wallet_balance' => User::where('wallet_balance', '>', 0)->avg('wallet_balance') ?? 0,
            'total_successful_transactions' => Transaction::where('status', 'success')->sum('amount'),
            'total_withdrawals' => Withdraw::where('status', 'approved')->sum('amount'),
            'total_wallets' => Wallet::count(),
            'wallets_with_balance' => User::where('wallet_balance', '>', 0)->count(),
        ];

        // Referral Analytics (Overall)
        $referralStats = [
            'total_referrals' => Referral::count(),
            'total_signup_rewards' => Referral::where('reward_type', 'signup')->where('status', 'paid')->sum('reward_amount'),
            'total_transaction_commission' => Referral::where('reward_type', 'transaction')->where('status', 'paid')->sum('reward_amount'),
            'total_referral_payout' => Referral::where('status', 'paid')->sum('reward_amount'),
        ];

        // User Wallet Details (Top 10 by balance)
        $topWallets = User::with('wallet')
            ->orderBy('wallet_balance', 'desc')
            ->where('wallet_balance', '>', 0)
            ->limit(10)
            ->get(['id', 'name', 'email', 'wallet_balance']);

        // Referral Leaderboard (Top 10) - Calculate per user
        $referralLeaderboard = User::with(['referralsMade' => function($query) {
                $query->where('status', 'paid');
            }, 'referredUsers'])
            ->has('referralsMade')
            ->get()
            ->map(function($user) {
                $paidReferrals = $user->referralsMade->where('status', 'paid');
                $signupRewards = $paidReferrals->where('reward_type', 'signup');
                $transactionRewards = $paidReferrals->where('reward_type', 'transaction');
                
                return [
                    'user' => $user,
                    'total_referrals' => $paidReferrals->count(),
                    'total_commission' => $paidReferrals->sum('reward_amount'),
                    'signup_count' => $signupRewards->count(),
                    'signup_total' => $signupRewards->sum('reward_amount'),
                    'transaction_count' => $transactionRewards->count(),
                    'transaction_total' => $transactionRewards->sum('reward_amount'),
                ];
            })
            ->sortByDesc('total_commission')
            ->take(10);

        // Per-user referral details (Detailed breakdown)
        $userReferralDetails = User::with(['referralsMade' => function($query) {
                $query->where('status', 'paid');
            }, 'referredUsers'])
            ->has('referralsMade')
            ->get()
            ->map(function($user) {
                $paidReferrals = $user->referralsMade->where('status', 'paid');
                $signupRewards = $paidReferrals->where('reward_type', 'signup');
                $transactionRewards = $paidReferrals->where('reward_type', 'transaction');
                
                // Count unique referred users who made purchases
                $referredBuyers = User::where('referred_by', $user->id)
                    ->whereHas('transactionsAsBuyer', function($q) {
                        $q->where('status', 'success');
                    })
                    ->count();
                
                return [
                    'user' => $user,
                    'total_signups' => $user->referredUsers->count(),
                    'signup_count' => $signupRewards->count(),
                    'signup_total' => $signupRewards->sum('reward_amount'),
                    'transaction_count' => $transactionRewards->count(),
                    'transaction_total' => $transactionRewards->sum('reward_amount'),
                    'total_commission' => $paidReferrals->sum('reward_amount'),
                    'referred_buyers_count' => $referredBuyers,
                ];
            })
            ->sortByDesc('total_commission')
            ->take(20);

        // Daily Note Creation (Last 30 days)
        $dailyNotes = Note::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Notes per User (Top 10)
        $topNoteCreators = User::withCount('notes')
            ->whereHas('notes')
            ->orderBy('notes_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        // Daily notes per user (detailed)
        $userNoteActivity = Note::select(
                'user_id',
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as note_count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id', 'date')
            ->with('user:id,name,email')
            ->orderBy('date', 'desc')
            ->orderBy('note_count', 'desc')
            ->get()
            ->groupBy('date');

        // Revenue Analytics (Last 30 days)
        $revenueData = Transaction::where('status', 'success')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Top Sellers (by revenue)
        $topSellers = User::with(['transactionsAsSeller' => function($query) {
                $query->where('status', 'success');
            }])
            ->has('transactionsAsSeller')
            ->get()
            ->map(function($user) {
                $successfulSales = $user->transactionsAsSeller->where('status', 'success');
                return [
                    'user' => $user,
                    'total_sales' => $successfulSales->sum('amount'),
                    'sales_count' => $successfulSales->count(),
                ];
            })
            ->sortByDesc('total_sales')
            ->take(10)
            ->values();

        // Top Buyers (by spending)
        $topBuyers = User::with(['transactionsAsBuyer' => function($query) {
                $query->where('status', 'success');
            }])
            ->has('transactionsAsBuyer')
            ->get()
            ->map(function($user) {
                $successfulPurchases = $user->transactionsAsBuyer->where('status', 'success');
                return [
                    'user' => $user,
                    'total_spent' => $successfulPurchases->sum('amount'),
                    'purchase_count' => $successfulPurchases->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(10)
            ->values();

        // User Growth (Last 30 days)
        $userGrowth = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Recent transactions
        $recentTransactions = Transaction::with(['buyer', 'seller', 'note'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent withdraws
        $recentWithdraws = Withdraw::with(['user', 'processedBy'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'platformBalance',
            'walletStats',
            'referralStats',
            'topWallets',
            'referralLeaderboard',
            'userReferralDetails',
            'dailyNotes',
            'topNoteCreators',
            'userNoteActivity',
            'revenueData',
            'topSellers',
            'topBuyers',
            'userGrowth',
            'recentTransactions',
            'recentWithdraws'
        ));
    }
}
