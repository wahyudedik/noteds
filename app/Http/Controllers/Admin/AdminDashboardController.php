<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ContentReport;
use App\Models\Post;
use App\Models\AuditLog;
use App\Services\AdminAnalyticsService;
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
        $totalUsers = User::count();

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

        // Recent Data
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

        // Analytics Trends
        $userGrowthTrends = $this->analyticsService->getUserGrowthTrends('monthly');
        $postTrends = $this->analyticsService->getPostCreationTrends('monthly');
        $engagementMetrics = $this->analyticsService->getEngagementMetrics();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                // Basic Stats
                'total_users' => $totalUsers,
                
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
            ],
            'recent_reports' => $recentReports,
            'recent_users' => $recentUsers,
            'recent_activities' => $recentActivities,
            'analytics' => [
                'user_growth_trends' => $userGrowthTrends,
                'post_trends' => $postTrends,
                'engagement_metrics' => $engagementMetrics,
            ],
        ]);
    }
}
