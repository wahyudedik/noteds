<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Check and award badges for a user based on sales count.
     */
    public function checkSalesBadges(User $user): array
    {
        $awarded = [];
        
        // Get user's successful sales count
        $salesCount = $user->transactionsAsSeller()
            ->where('status', 'success')
            ->count();

        // Get all milestone badges for sales
        $badges = Badge::where('category', 'milestone')
            ->where('criteria_type', 'sales_count')
            ->where('is_active', true)
            ->orderBy('criteria_value')
            ->get();

        foreach ($badges as $badge) {
            if ($salesCount >= $badge->criteria_value && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge, "Achieved {$salesCount} sales");
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Check and award quality badges for a user.
     */
    public function checkQualityBadges(User $user): array
    {
        $awarded = [];

        // Get user's average rating from all public notes
        $notes = $user->notes()->where('is_public', true)->pluck('id');
        
        if ($notes->isEmpty()) {
            return $awarded;
        }

        $reviews = \App\Models\NoteReview::whereIn('note_id', $notes)->get();
        $totalReviews = $reviews->count();
        $totalRating = $reviews->sum('rating');
        $averageRating = $totalReviews > 0 ? $totalRating / $totalReviews : 0;

        // Get total sales count
        $salesCount = $user->transactionsAsSeller()->where('status', 'success')->count();

        // Check 5-Star Seller badge (4.5+ rating with 10+ reviews)
        if ($averageRating >= 4.5 && $totalReviews >= 10) {
            $badge = Badge::where('slug', '5-star-seller')->where('is_active', true)->first();
            if ($badge && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge, "Average rating: " . number_format($averageRating, 1) . " with {$totalReviews} reviews");
                $awarded[] = $badge;
            }
        }

        // Check Top Rated badge (4.0+ rating with 50+ reviews)
        if ($averageRating >= 4.0 && $totalReviews >= 50) {
            $badge = Badge::where('slug', 'top-rated')->where('is_active', true)->first();
            if ($badge && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge, "Average rating: " . number_format($averageRating, 1) . " with {$totalReviews} reviews");
                $awarded[] = $badge;
            }
        }

        // Check Best Seller badge (top 10% of sellers by sales)
        if ($salesCount > 0) {
            $totalSellers = User::where('role', 'seller')->count();
            $topSellers = User::where('role', 'seller')
                ->withCount(['transactionsAsSeller' => function($q) {
                    $q->where('status', 'success');
                }])
                ->orderBy('transactions_as_seller_count', 'desc')
                ->limit(max(1, (int)($totalSellers * 0.1)))
                ->pluck('id');
            
            if ($topSellers->contains($user->id)) {
                $badge = Badge::where('slug', 'best-seller')->where('is_active', true)->first();
                if ($badge && !$user->hasBadge($badge)) {
                    $this->awardBadge($user, $badge, "Top 10% seller with {$salesCount} sales");
                    $awarded[] = $badge;
                }
            }
        }

        return $awarded;
    }

    /**
     * Check and award community badges for a user.
     */
    public function checkCommunityBadges(User $user): array
    {
        $awarded = [];

        // Check Helpful Reviewer badge
        // Count reviews that have helpful replies
        $helpfulReviews = \App\Models\NoteReview::where('user_id', $user->id)
            ->whereHas('replies', function($q) {
                $q->where('is_helpful', true);
            })
            ->count();

        if ($helpfulReviews >= 10) {
            $badge = Badge::where('slug', 'helpful-reviewer')->where('is_active', true)->first();
            if ($badge && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge, "{$helpfulReviews} helpful reviews");
                $awarded[] = $badge;
            }
        }

        // Check Active User badge (logged in within last 7 days and has activity)
        $daysSinceLastActivity = $user->updated_at->diffInDays(now());
        if ($daysSinceLastActivity <= 7) {
            // Count various activities
            $activities = 0;
            $activities += $user->notes()->where('created_at', '>=', now()->subDays(7))->count();
            $activities += $user->posts()->where('created_at', '>=', now()->subDays(7))->count();
            $activities += \App\Models\NoteReview::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))->count();
            
            if ($activities >= 5) {
                $badge = Badge::where('slug', 'active-user')->where('is_active', true)->first();
                if ($badge && !$user->hasBadge($badge)) {
                    $this->awardBadge($user, $badge, "Active in the last 7 days with {$activities} activities");
                    $awarded[] = $badge;
                }
            }
        }

        // Check Community Helper badge (answered questions, helpful replies, etc.)
        $helpfulAnswers = \App\Models\NoteQuestion::where('user_id', $user->id)
            ->whereHas('answers', function($q) {
                $q->where('is_helpful', true);
            })
            ->count();

        if ($helpfulAnswers >= 5) {
            $badge = Badge::where('slug', 'community-helper')->where('is_active', true)->first();
            if ($badge && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge, "{$helpfulAnswers} helpful answers");
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Award a badge to a user.
     */
    public function awardBadge(User $user, Badge $badge, ?string $notes = null): UserBadge
    {
        // Check if user already has this badge
        if ($user->hasBadge($badge)) {
            return $user->userBadges()->where('badge_id', $badge->id)->first();
        }

        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
            'notes' => $notes,
        ]);

        // Send notification
        $this->notificationService->notifyBadgeEarned($user, $badge);

        return $userBadge;
    }

    /**
     * Check all badges for a user.
     */
    public function checkAllBadges(User $user): array
    {
        $awarded = [];

        $awarded = array_merge($awarded, $this->checkSalesBadges($user));
        $awarded = array_merge($awarded, $this->checkQualityBadges($user));
        $awarded = array_merge($awarded, $this->checkCommunityBadges($user));
        $awarded = array_merge($awarded, $this->checkCustomBadges($user));

        return $awarded;
    }

    /**
     * Check and award custom badges for a user.
     * Custom badges are manually awarded by admin, but we can check criteria if set.
     */
    public function checkCustomBadges(User $user): array
    {
        $awarded = [];

        // Get all active custom badges with criteria
        $customBadges = Badge::where('is_custom', true)
            ->where('is_active', true)
            ->whereNotNull('custom_criteria')
            ->get();

        foreach ($customBadges as $badge) {
            if ($user->hasBadge($badge)) {
                continue;
            }

            $criteria = $badge->custom_criteria;
            $meetsCriteria = true;

            // Check each criteria
            if (isset($criteria['min_sales']) && $criteria['min_sales'] > 0) {
                $salesCount = $user->transactionsAsSeller()->where('status', 'success')->count();
                if ($salesCount < $criteria['min_sales']) {
                    $meetsCriteria = false;
                }
            }

            if (isset($criteria['min_rating']) && $criteria['min_rating'] > 0) {
                $notes = $user->notes()->where('is_public', true)->pluck('id');
                if ($notes->isNotEmpty()) {
                    $avgRating = \App\Models\NoteReview::whereIn('note_id', $notes)->avg('rating');
                    if ($avgRating < $criteria['min_rating']) {
                        $meetsCriteria = false;
                    }
                } else {
                    $meetsCriteria = false;
                }
            }

            if ($meetsCriteria) {
                $this->awardBadge($user, $badge, "Met custom criteria");
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Manually award a badge to a user (for custom badges).
     */
    public function manuallyAwardBadge(User $user, Badge $badge, ?string $notes = null): UserBadge
    {
        return $this->awardBadge($user, $badge, $notes);
    }

    /**
     * Get user's badges grouped by category.
     */
    public function getUserBadgesGrouped(User $user): array
    {
        return [
            'milestone' => $user->getBadgesByCategory('milestone'),
            'quality' => $user->getBadgesByCategory('quality'),
            'community' => $user->getBadgesByCategory('community'),
        ];
    }
}

