<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteSharePurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GrowthHackingService
{
    /**
     * Referral Rewards System
     */
    public function processReferralBonus(User $referee, User $referrer): void
    {
        $bonusAmount = 50000; // IDR
        $referrerLevel = $referrer->level ?? 1;

        // Bonus multiplier based on referrer level
        $multiplier = match ($referrerLevel) {
            1 => 1.0,    // Bronze: 1x
            2 => 1.2,    // Silver: 1.2x
            3 => 1.5,    // Gold: 1.5x
            4 => 2.0,    // Platinum: 2x
            default => 1.0,
        };

        $finalBonus = $bonusAmount * $multiplier;

        // Credit to referrer wallet
        $referrer->wallet->credit($finalBonus, 'referral_bonus', [
            'referee_id' => $referee->id,
            'bonus_multiplier' => $multiplier,
        ]);

        // Credit to referee wallet (smaller welcome bonus)
        $referee->wallet->credit(25000, 'welcome_bonus', [
            'referrer_id' => $referrer->id,
        ]);

        // Track in referral system
        DB::table('referrals')->insert([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'bonus_amount' => $finalBonus,
            'referee_bonus' => 25000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Content Sharing Viral Loop
     * When user shares a note to get discounted purchase
     */
    public function shareToUnlockDiscount(User $user, Note $note, int $sharesToUnlock = 3): array
    {
        $sharesCompleted = DB::table('note_share_purchases')
            ->where('user_id', $user->id)
            ->where('note_id', $note->id)
            ->count();

        $sharesRemaining = max(0, $sharesToUnlock - $sharesCompleted);
        $discountPercentage = max(0, (1 - ($sharesRemaining / $sharesToUnlock)) * 30);

        return [
            'shares_completed' => $sharesCompleted,
            'shares_remaining' => $sharesRemaining,
            'discount_percentage' => $discountPercentage,
            'discount_unlocked' => $sharesRemaining === 0,
            'share_link' => route('notes.shared', [
                'note' => $note->slug,
                'from' => $user->username,
                'share_type' => 'discount',
            ]),
        ];
    }

    /**
     * Gamification: Streak Rewards
     * Reward consistent daily engagement
     */
    public function processStreakRewards(User $user): void
    {
        $currentDate = today();
        $lastActivityDate = $user->lastActivityDate();

        if (!$lastActivityDate) {
            $user->current_streak = 1;
            $user->save();
            return;
        }

        $daysDifference = $currentDate->diffInDays($lastActivityDate);

        if ($daysDifference === 1) {
            // Continue streak
            $user->current_streak = ($user->current_streak ?? 0) + 1;
        } elseif ($daysDifference > 1) {
            // Streak broken, start new
            $user->current_streak = 1;
        }
        // Same day activity doesn't increase streak

        $user->save();

        // Milestone rewards
        $streakMilestones = [7, 14, 30, 60, 100];
        if (in_array($user->current_streak, $streakMilestones)) {
            $this->grantStreakMilestoneReward($user, $user->current_streak);
        }
    }

    /**
     * Grant streak milestone rewards
     */
    private function grantStreakMilestoneReward(User $user, int $streak): void
    {
        $rewards = [
            7 => ['points' => 500, 'badge' => 'week_warrior'],
            14 => ['points' => 1500, 'badge' => 'fortnight_fighter'],
            30 => ['points' => 5000, 'badge' => 'monthly_master', 'cash' => 100000],
            60 => ['points' => 15000, 'badge' => 'bimonthly_boss', 'cash' => 250000],
            100 => ['points' => 50000, 'badge' => 'century_champion', 'cash' => 1000000],
        ];

        $reward = $rewards[$streak] ?? null;
        if (!$reward) return;

        // Award points
        $user->addPoints($reward['points'] ?? 0, "streak_{$streak}_reward");

        // Award badge
        if (isset($reward['badge'])) {
            $user->awardBadge($reward['badge']);
        }

        // Award cash
        if (isset($reward['cash'])) {
            $user->wallet->credit($reward['cash'], 'streak_milestone', ['streak' => $streak]);
        }

        // Send notification
        $user->notify(new StreakMilestoneNotification($streak, $reward));
    }

    /**
     * First-Time Buyer Incentive
     */
    public function getFirstBuyerIncentive(User $user): ?array
    {
        $hasPurchased = DB::table('purchased_notes')->where('user_id', $user->id)->exists();

        if ($hasPurchased) {
            return null; // Already a buyer
        }

        return [
            'discount_percentage' => 20,
            'bonus_points' => 500,
            'message' => 'Welcome! Get 20% off your first purchase + 500 bonus points',
            'coupon_code' => 'FIRST20',
            'valid_until' => now()->addDays(7),
        ];
    }

    /**
     * Engagement Nudge: Users with low activity
     */
    public function sendEngagementNudges(): void
    {
        $inactiveUsers = User::where('last_login_at', '<', now()->subDays(7))
            ->where('created_at', '<', now()->subDays(3))
            ->limit(1000)
            ->get();

        foreach ($inactiveUsers as $user) {
            $this->sendEngagementEmail($user);
        }
    }

    /**
     * Send personalized re-engagement email
     */
    private function sendEngagementEmail(User $user): void
    {
        // Get personalized content for user
        $recommendedNotes = (new ContentRecommendationEngine)
            ->getPersonalizedRecommendations($user, 3);

        if ($recommendedNotes->isEmpty()) {
            return; // No recommendations available
        }

        // Send email with recommendations + discount offer
        Mail::queue(new ReEngagementEmail($user, $recommendedNotes, [
            'discount' => 15,
            'valid_until' => now()->addDays(3),
        ]));
    }

    /**
     * Partner/Influencer Tracking
     * Track conversions from influencer referrals
     */
    public function trackInfluencerConversion(string $influencerCode, User $user): void
    {
        $affiliate = DB::table('affiliates')
            ->where('code', $influencerCode)
            ->first();

        if (!$affiliate) {
            return;
        }

        // Mark user as referred by influencer
        DB::table('influencer_conversions')->insert([
            'influencer_id' => $affiliate->user_id,
            'user_id' => $user->id,
            'code' => $influencerCode,
            'created_at' => now(),
        ]);

        // Grant referrer bonus
        $referrer = User::find($affiliate->user_id);
        if ($referrer) {
            $referrer->wallet->credit(50000, 'influencer_referral', [
                'influenced_user' => $user->id,
            ]);
        }
    }

    /**
     * Viral Loop: Users refer 3 friends = premium month free
     */
    public function checkViraRewardUnlock(User $user): ?array
    {
        $successfulReferrals = DB::table('referrals')
            ->where('referrer_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($successfulReferrals >= 3 && !$user->hasClaimed('viral_loop_bonus')) {
            return [
                'reward_type' => 'premium_month',
                'value' => 99000,
                'message' => 'Congratulations! You earned a free month of Premium!',
                'action_url' => route('subscription.claim-bonus'),
            ];
        }

        return null;
    }

    /**
     * Event Participation Rewards
     * Limited-time challenges to drive engagement
     */
    public function getCurrentEventChallenge(): ?array
    {
        return Cache::remember('current_event_challenge', 3600, function () {
            $challenge = DB::table('event_challenges')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('active', true)
                ->latest()
                ->first();

            if (!$challenge) {
                return null;
            }

            return [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'description' => $challenge->description,
                'duration_days' => $challenge->start_date->diffInDays($challenge->end_date),
                'requirements' => json_decode($challenge->requirements),
                'rewards' => json_decode($challenge->rewards),
                'participants' => DB::table('challenge_participants')
                    ->where('challenge_id', $challenge->id)
                    ->count(),
            ];
        });
    }

    /**
     * Content Quality Bonus
     * Reward creators with high-quality content
     */
    public function processQualityBonuses(): void
    {
        $creators = User::whereHas('notes', function ($q) {
            $q->where('status', 'published')
                ->where('updated_at', '>=', now()->subDays(7))
                ->withCount('purchases', 'reviews')
                ->having('purchases_count', '>=', 10)
                ->having('reviews_count', '>=', 5);
        })->get();

        foreach ($creators as $creator) {
            $bonus = 200000; // Base bonus

            // Calculate based on sales and reviews
            $topNotes = $creator->notes()
                ->where('published', true)
                ->withCount('purchases', 'reviews')
                ->orderByDesc('purchases_count')
                ->limit(5)
                ->get();

            foreach ($topNotes as $note) {
                $bonus += ($note->purchases_count * 5000) + ($note->reviews_count * 10000);
            }

            $creator->wallet->credit($bonus, 'quality_bonus', [
                'period' => 'weekly',
            ]);
        }
    }

    /**
     * Get user growth metrics
     */
    public function getGrowthMetrics(): array
    {
        return [
            'this_week' => [
                'new_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'new_content' => Note::where('created_at', '>=', now()->subDays(7))->count(),
                'conversions' => DB::table('purchased_notes')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ],
            'this_month' => [
                'new_users' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'new_content' => Note::where('created_at', '>=', now()->startOfMonth())->count(),
                'revenue' => DB::table('transactions')
                    ->where('created_at', '>=', now()->startOfMonth())
                    ->sum('amount'),
            ],
            'viral_coefficient' => [
                'k_factor' => $this->calculateVirality(),
                'referral_rate' => $this->getReferralConversionRate(),
                'share_rate' => $this->getShareRate(),
            ],
        ];
    }

    /**
     * Calculate viral coefficient (K-factor)
     */
    private function calculateVirality(): float
    {
        $totalInvites = DB::table('referrals')->count();
        $successfulConversions = DB::table('referrals')
            ->whereNotNull('referee_id')
            ->count();

        if ($totalInvites === 0) {
            return 0;
        }

        return $successfulConversions / $totalInvites;
    }

    /**
     * Get referral conversion rate
     */
    private function getReferralConversionRate(): float
    {
        $invites = DB::table('invitations')->count();
        $conversions = DB::table('users')->where('referred_by_user_id', '!=', null)->count();

        if ($invites === 0) {
            return 0;
        }

        return ($conversions / $invites) * 100;
    }

    /**
     * Get share rate
     */
    private function getShareRate(): float
    {
        $users = User::count();
        $sharers = DB::table('note_share_purchases')->distinct('user_id')->count('user_id');

        if ($users === 0) {
            return 0;
        }

        return ($sharers / $users) * 100;
    }
}
