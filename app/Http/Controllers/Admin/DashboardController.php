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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Jobs\CalculateStatisticsJob;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Try to get cached stats first, otherwise calculate
        $cacheKey = 'dashboard_stats';
        $cachedStats = Cache::get($cacheKey);
        
        // Dispatch job to refresh stats in background if cache is old or missing
        if (!$cachedStats || Cache::get("{$cacheKey}_last_updated", 0) < now()->subMinutes(5)->timestamp) {
            CalculateStatisticsJob::dispatch('dashboard')
                ->onQueue('statistics');
        }
        
        // Use cached stats if available, otherwise calculate synchronously
        if ($cachedStats) {
            $stats = $cachedStats;
        } else {
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
        }

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

        // Topup History (Last 50 transactions)
        $topupHistory = Transaction::where('payment_method', 'topup')
            ->with(['buyer'])
            ->latest()
            ->limit(50)
            ->get();

        // Topup Statistics
        $topupStats = [
            'total_topups' => Transaction::where('payment_method', 'topup')->count(),
            'successful_topups' => Transaction::where('payment_method', 'topup')
                ->where('status', 'success')
                ->count(),
            'pending_topups' => Transaction::where('payment_method', 'topup')
                ->where('status', 'pending')
                ->count(),
            'failed_topups' => Transaction::where('payment_method', 'topup')
                ->where('status', 'failed')
                ->count(),
            'total_topup_amount' => Transaction::where('payment_method', 'topup')
                ->where('status', 'success')
                ->sum('amount'),
            'total_topup_today' => Transaction::where('payment_method', 'topup')
                ->where('status', 'success')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'total_topup_this_month' => Transaction::where('payment_method', 'topup')
                ->where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];

        // Midtrans Statistics (All transactions via Midtrans)
        $midtransStats = [
            'total_midtrans_transactions' => Transaction::whereNotNull('midtrans_order_id')->count(),
            'successful_midtrans_transactions' => Transaction::whereNotNull('midtrans_order_id')
                ->where('status', 'success')
                ->count(),
            'total_midtrans_amount' => Transaction::whereNotNull('midtrans_order_id')
                ->where('status', 'success')
                ->sum('amount'),
            'total_midtrans_today' => Transaction::whereNotNull('midtrans_order_id')
                ->where('status', 'success')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'total_midtrans_this_month' => Transaction::whereNotNull('midtrans_order_id')
                ->where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
            'total_midtrans_commission' => Transaction::whereNotNull('midtrans_order_id')
                ->where('status', 'success')
                ->sum('commission'),
        ];

        // Topup by Type (Top-up vs Purchase)
        $topupByType = Transaction::whereNotNull('midtrans_order_id')
            ->where('status', 'success')
            ->select(
                DB::raw('CASE 
                    WHEN payment_method = "topup" THEN "Top-up"
                    WHEN payment_method = "purchase" THEN "Purchase"
                    WHEN payment_method = "subscription" THEN "Subscription"
                    ELSE "Other"
                END as type'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('type')
            ->get();

        // Sale Mode Analytics
        $saleModeStats = [
            // Notes count by sale mode
            'scarcity_notes' => Note::where('sale_mode', 'scarcity')->count(),
            'standard_notes' => Note::where('sale_mode', 'standard')->count(),
            'total_with_sale_mode' => Note::whereNotNull('sale_mode')->count(),
            
            // Transactions by sale mode (via notes)
            'scarcity_transactions' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->count(),
            'standard_transactions' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'standard');
                })
                ->count(),
            
            // Revenue by sale mode
            'scarcity_revenue' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->sum('platform_fee'),
            'standard_revenue' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'standard');
                })
                ->sum('platform_fee'),
            
            // Total amount by sale mode
            'scarcity_total_amount' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->sum('amount'),
            'standard_total_amount' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'standard');
                })
                ->sum('amount'),
            
            // Creator commission (only scarcity mode)
            'scarcity_creator_commission' => Transaction::where('status', 'success')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->sum('creator_commission'),
            
            // Resale statistics (scarcity mode only)
            'resale_count' => Transaction::where('status', 'success')
                ->whereNotNull('resale_price')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->count(),
            'resale_revenue' => Transaction::where('status', 'success')
                ->whereNotNull('resale_price')
                ->whereHas('note', function($q) {
                    $q->where('sale_mode', 'scarcity');
                })
                ->sum('platform_fee'),
            
            // Repurchase statistics (simplified - count transactions with grace_period_ends_at where buyer has previous transaction)
            'repurchase_count' => DB::table('transactions as t1')
                ->join('notes', 't1.note_id', '=', 'notes.id')
                ->where('t1.status', 'success')
                ->where('notes.sale_mode', 'scarcity')
                ->whereNotNull('t1.grace_period_ends_at')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('transactions as t2')
                        ->whereColumn('t2.note_id', 't1.note_id')
                        ->whereColumn('t2.buyer_id', 't1.buyer_id')
                        ->where('t2.status', 'success')
                        ->where('t2.id', '<', DB::raw('t1.id'));
                })
                ->count(),
        ];

        // Calculate averages
        $saleModeStats['scarcity_avg_price'] = $saleModeStats['scarcity_transactions'] > 0 
            ? $saleModeStats['scarcity_total_amount'] / $saleModeStats['scarcity_transactions'] 
            : 0;
        $saleModeStats['standard_avg_price'] = $saleModeStats['standard_transactions'] > 0 
            ? $saleModeStats['standard_total_amount'] / $saleModeStats['standard_transactions'] 
            : 0;

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
            'recentWithdraws',
            'topupHistory',
            'topupStats',
            'midtransStats',
            'topupByType',
            'saleModeStats'
        ));
    }

    public function repurchaseReport(Request $request): View
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get all repurchase transactions (transactions where buyer has previous transaction for same note)
        $repurchaseTransactions = Transaction::with(['buyer', 'seller', 'note', 'originalCreator'])
            ->where('status', 'success')
            ->whereHas('note', function($q) {
                $q->where('sale_mode', 'scarcity');
            })
            ->whereNotNull('grace_period_ends_at')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('transactions as t2')
                    ->whereColumn('t2.note_id', 'transactions.note_id')
                    ->whereColumn('t2.buyer_id', 'transactions.buyer_id')
                    ->where('t2.status', 'success')
                    ->where('t2.id', '<', DB::raw('transactions.id'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $totalRepurchases = $repurchaseTransactions->count();
        $totalRepurchaseRevenue = $repurchaseTransactions->sum('platform_fee');
        $totalRepurchaseAmount = $repurchaseTransactions->sum('amount');
        $avgRepurchasePrice = $totalRepurchases > 0 ? $totalRepurchaseAmount / $totalRepurchases : 0;

        // Calculate average time to repurchase
        $repurchaseTimes = [];
        foreach ($repurchaseTransactions as $repurchase) {
            $firstPurchase = Transaction::where('note_id', $repurchase->note_id)
                ->where('buyer_id', $repurchase->buyer_id)
                ->where('status', 'success')
                ->where('id', '<', $repurchase->id)
                ->orderBy('created_at', 'asc')
                ->first();
            
            if ($firstPurchase) {
                $daysDiff = $repurchase->created_at->diffInDays($firstPurchase->created_at);
                $repurchaseTimes[] = $daysDiff;
            }
        }
        $avgTimeToRepurchase = count($repurchaseTimes) > 0 ? array_sum($repurchaseTimes) / count($repurchaseTimes) : 0;

        // Get total scarcity transactions for repurchase rate calculation
        $totalScarcityTransactions = Transaction::where('status', 'success')
            ->whereHas('note', function($q) {
                $q->where('sale_mode', 'scarcity');
            })
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();
        
        $repurchaseRate = $totalScarcityTransactions > 0 
            ? ($totalRepurchases / $totalScarcityTransactions * 100) 
            : 0;

        // Group by note
        $repurchasesByNote = $repurchaseTransactions->groupBy('note_id')->map(function($transactions) {
            return [
                'note' => $transactions->first()->note,
                'count' => $transactions->count(),
                'revenue' => $transactions->sum('platform_fee'),
                'total_amount' => $transactions->sum('amount'),
            ];
        })->sortByDesc('count')->take(10);

        // Group by buyer
        $repurchasesByBuyer = $repurchaseTransactions->groupBy('buyer_id')->map(function($transactions) {
            return [
                'buyer' => $transactions->first()->buyer,
                'count' => $transactions->count(),
                'total_spent' => $transactions->sum('amount'),
            ];
        })->sortByDesc('count')->take(10);

        // Repurchases within grace period vs after grace period
        $withinGracePeriod = $repurchaseTransactions->filter(function($transaction) {
            if (!$transaction->grace_period_ends_at) return false;
            $firstPurchase = Transaction::where('note_id', $transaction->note_id)
                ->where('buyer_id', $transaction->buyer_id)
                ->where('status', 'success')
                ->where('id', '<', $transaction->id)
                ->orderBy('created_at', 'asc')
                ->first();
            
            if (!$firstPurchase || !$firstPurchase->grace_period_ends_at) return false;
            return $transaction->created_at->lte($firstPurchase->grace_period_ends_at);
        });

        $afterGracePeriod = $repurchaseTransactions->filter(function($transaction) {
            if (!$transaction->grace_period_ends_at) return false;
            $firstPurchase = Transaction::where('note_id', $transaction->note_id)
                ->where('buyer_id', $transaction->buyer_id)
                ->where('status', 'success')
                ->where('id', '<', $transaction->id)
                ->orderBy('created_at', 'asc')
                ->first();
            
            if (!$firstPurchase || !$firstPurchase->grace_period_ends_at) return true;
            return $transaction->created_at->gt($firstPurchase->grace_period_ends_at);
        });

        $stats = [
            'total_repurchases' => $totalRepurchases,
            'total_revenue' => $totalRepurchaseRevenue,
            'total_amount' => $totalRepurchaseAmount,
            'avg_price' => $avgRepurchasePrice,
            'avg_time_days' => round($avgTimeToRepurchase, 1),
            'repurchase_rate' => round($repurchaseRate, 2),
            'within_grace_period' => $withinGracePeriod->count(),
            'after_grace_period' => $afterGracePeriod->count(),
            'within_grace_period_revenue' => $withinGracePeriod->sum('platform_fee'),
            'after_grace_period_revenue' => $afterGracePeriod->sum('platform_fee'),
        ];

        return view('admin.repurchase-report', compact(
            'repurchaseTransactions',
            'stats',
            'repurchasesByNote',
            'repurchasesByBuyer',
            'startDate',
            'endDate'
        ));
    }
}
