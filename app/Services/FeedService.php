<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedService
{
    /**
     * Purpose type labels mapping
     */
    private function getPurposeTypeLabels(): array
    {
        return [
            'idea_business' => '💡 Ide Bisnis',
            'ask_question' => '❓ Tanya Masalah Bisnis',
            'share_experience' => '📈 Sharing Pengalaman',
            'find_partner' => '🤝 Cari Partner',
            'find_tools' => '🛠 Cari Tools / Resource',
            'validate_idea' => '🧪 Validasi Ide',
        ];
    }

    /**
     * Get trending topics (purpose types by count)
     */
    public function getTrendingTopics(int $days = 7, int $limit = 5): Collection
    {
        $purposeTypeLabels = $this->getPurposeTypeLabels();

        return Post::where('status', 'active')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select('purpose_type', DB::raw('count(*) as count'))
            ->groupBy('purpose_type')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($purposeTypeLabels) {
                return [
                    'id' => $item->purpose_type,
                    'name' => $purposeTypeLabels[$item->purpose_type] ?? $item->purpose_type,
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Get suggested users (users with most posts in last N days)
     */
    public function getSuggestedUsers(int $days = 30, int $limit = 5): Collection
    {
        return User::whereHas('posts', function ($query) use ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        })
            ->withCount(['posts' => function ($query) use ($days) {
                $query->where('created_at', '>=', Carbon::now()->subDays($days));
            }])
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get quick stats for widget
     */
    public function getQuickStats(User $user = null): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $postsToday = Post::where('status', 'active')
            ->whereDate('created_at', $today)
            ->count();

        $postsThisWeek = Post::where('status', 'active')
            ->where('created_at', '>=', $startOfWeek)
            ->count();

        $totalUsers = User::whereHas('posts', function ($query) use ($thirtyDaysAgo) {
            $query->where('status', 'active')
                ->where('created_at', '>=', $thirtyDaysAgo);
        })
            ->distinct()
            ->count();

        $totalPosts = Post::where('status', 'active')->count();

        return [
            'posts_today' => $postsToday,
            'posts_this_week' => $postsThisWeek,
            'total_users' => $totalUsers,
            'total_posts' => $totalPosts,
        ];
    }
}
