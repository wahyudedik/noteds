<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ContentReport;
use App\Models\Post;
use App\Models\Campaign;
use App\Models\Clip;
use App\Models\BrandRegistration;
use App\Models\ClipperRegistration;
use App\Models\AuditLog;
use App\Models\PlatformWallet;
use App\Models\LedgerEntry;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct(
        private AdminAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        // Basic Statistics
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $totalUsers = User::count();
        $totalSales = Order::where('payment_status', 'paid')->sum('total');
        $totalProducts = Product::count();

        // Reports Statistics
        $pendingReports = ContentReport::where('status', 'pending')->count();
        $pendingPostReports = ContentReport::where('status', 'pending')
            ->where('reportable_type', 'App\Models\Post')
            ->count();
        $pendingCommentReports = ContentReport::where('status', 'pending')
            ->where('reportable_type', 'App\Models\Comment')
            ->count();
        $pendingUserReports = ContentReport::where('status', 'pending')
            ->where('reportable_type', 'App\Models\User')
            ->count();

        // Posts Statistics
        $pendingPostsModeration = Post::whereIn('status', ['moderated', 'archived'])->count();
        $totalPosts = Post::count();
        $activePosts = Post::where('status', 'active')->count();

        // User Statistics
        $bannedUsers = User::where('is_banned', true)->count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $newUsersThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $adminUsers = User::where('role', 'admin')->count();
        $regularUsers = User::where('role', 'user')->count();

        // Withdrawal Statistics by Type
        $pendingClipperWithdrawals = Withdrawal::where('status', 'pending')
            ->where('user_type', 'clipper')
            ->count();
        $pendingCreatorWithdrawals = Withdrawal::where('status', 'pending')
            ->where('user_type', 'creator')
            ->count();
        $pendingMarketplaceWithdrawals = Withdrawal::where('status', 'pending')
            ->where('user_type', 'seller')
            ->count();
        
        $pendingClipperAmount = Withdrawal::where('status', 'pending')
            ->where('user_type', 'clipper')
            ->sum('amount');
        $pendingCreatorAmount = Withdrawal::where('status', 'pending')
            ->where('user_type', 'creator')
            ->sum('amount');
        $pendingMarketplaceAmount = Withdrawal::where('status', 'pending')
            ->where('user_type', 'seller')
            ->sum('amount');

        // Sales Statistics
        $salesToday = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total');
        $salesThisWeek = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->sum('total');
        $salesThisMonth = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('total');

        // Clipper System Statistics
        $pendingClips = Clip::where('status', 'pending')->count();
        $pendingCampaigns = Campaign::where('status', 'draft')->count();
        $pendingBrandApprovals = BrandRegistration::where('status', 'pending')->count();
        $pendingClipperApprovals = ClipperRegistration::where('status', 'pending')->count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $totalClips = Clip::count();
        $totalCampaigns = Campaign::count();

        // Fraud Alerts Count - Optimized: Only check clips with recent activity
        $fraudAlertsCount = 0;
        try {
            if (class_exists(\App\Services\ViewValidationService::class)) {
                $viewValidationService = app(\App\Services\ViewValidationService::class);
                // Only check clips from last 30 days to avoid performance issues
                $recentClips = Clip::whereIn('status', ['pending', 'approved'])
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->limit(100) // Limit to prevent performance issues
                    ->get();
                
                foreach ($recentClips as $clip) {
                    try {
                        if (method_exists($viewValidationService, 'detectFraud') && $viewValidationService->detectFraud($clip)) {
                            $fraudAlertsCount++;
                        }
                    } catch (\Exception $e) {
                        // Skip if validation fails
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            // If service not available, set to 0
            $fraudAlertsCount = 0;
        }

        // Recent Data
        $recentWithdrawals = Withdrawal::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        $recentReports = ContentReport::with(['user', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::latest()
            ->limit(10)
            ->get();

        // Recent Activities from AuditLog
        $recentActivities = AuditLog::with(['admin', 'user'])
            ->whereNotNull('admin_id')
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'target_type' => $log->target_type,
                    'target_id' => $log->target_id,
                    'admin' => $log->admin ? [
                        'id' => $log->admin->id,
                        'name' => $log->admin->name,
                    ] : null,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ] : null,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at,
                ];
            });

        // Marketplace Commission Stats
        $platformWallet = PlatformWallet::getInstance();
        $marketplaceCommissionTotal = (float) $platformWallet->fee_balance;
        
        // Get marketplace commission from orders (since ledger uses 'fee' reason)
        $marketplaceCommissionThisMonth = (float) Order::whereNotNull('platform_commission_total')
            ->where('platform_commission_total', '>', 0)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('platform_commission_total');
        
        $averageCommissionPerOrder = 0;
        $totalOrdersWithCommission = Order::whereNotNull('platform_commission_total')
            ->where('platform_commission_total', '>', 0)
            ->count();
        if ($totalOrdersWithCommission > 0) {
            $averageCommissionPerOrder = (float) Order::whereNotNull('platform_commission_total')
                ->where('platform_commission_total', '>', 0)
                ->avg('platform_commission_total');
        }

        // Analytics Trends
        $userGrowthTrends = $this->analyticsService->getUserGrowthTrends('monthly');
        $salesTrends = $this->analyticsService->getSalesTrends('monthly');
        $postTrends = $this->analyticsService->getPostCreationTrends('monthly');
        $engagementMetrics = $this->analyticsService->getEngagementMetrics();
        $clipperMetrics = $this->analyticsService->getClipperSystemMetrics();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                // Basic Stats
                'pending_withdrawals' => $pendingWithdrawals,
                'total_users' => $totalUsers,
                'total_sales' => (float) $totalSales,
                'total_products' => $totalProducts,
                
                // Reports Stats
                'pending_reports' => $pendingReports,
                'pending_post_reports' => $pendingPostReports,
                'pending_comment_reports' => $pendingCommentReports,
                'pending_user_reports' => $pendingUserReports,
                
                // Posts Stats
                'pending_posts_moderation' => $pendingPostsModeration,
                'total_posts' => $totalPosts,
                'active_posts' => $activePosts,
                
                // User Stats
                'banned_users' => $bannedUsers,
                'new_users_today' => $newUsersToday,
                'new_users_this_week' => $newUsersThisWeek,
                'new_users_this_month' => $newUsersThisMonth,
                'admin_users' => $adminUsers,
                'regular_users' => $regularUsers,
                
                // Withdrawal Stats by Type
                'pending_clipper_withdrawals' => $pendingClipperWithdrawals,
                'pending_creator_withdrawals' => $pendingCreatorWithdrawals,
                'pending_marketplace_withdrawals' => $pendingMarketplaceWithdrawals,
                'pending_clipper_amount' => (float) $pendingClipperAmount,
                'pending_creator_amount' => (float) $pendingCreatorAmount,
                'pending_marketplace_amount' => (float) $pendingMarketplaceAmount,
                
                // Sales Stats
                'sales_today' => (float) $salesToday,
                'sales_this_week' => (float) $salesThisWeek,
                'sales_this_month' => (float) $salesThisMonth,
                
                // Clipper System Stats
                'pending_clips' => $pendingClips,
                'pending_campaigns' => $pendingCampaigns,
                'pending_brand_approvals' => $pendingBrandApprovals,
                'pending_clipper_approvals' => $pendingClipperApprovals,
                'active_campaigns' => $activeCampaigns,
                'total_clips' => $totalClips,
                'total_campaigns' => $totalCampaigns,
                'fraud_alerts_count' => $fraudAlertsCount,
                
                // Marketplace Commission Stats
                'marketplace_commission_total' => $marketplaceCommissionTotal,
                'marketplace_commission_this_month' => $marketplaceCommissionThisMonth,
                'average_commission_per_order' => $averageCommissionPerOrder,
            ],
            'recent_withdrawals' => $recentWithdrawals,
            'recent_reports' => $recentReports,
            'recent_users' => $recentUsers,
            'recent_activities' => $recentActivities,
            'analytics' => [
                'user_growth_trends' => $userGrowthTrends,
                'sales_trends' => $salesTrends,
                'post_trends' => $postTrends,
                'engagement_metrics' => $engagementMetrics,
                'clipper_metrics' => $clipperMetrics,
            ],
        ]);
    }
}
