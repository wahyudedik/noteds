<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteShareReferral;
use App\Models\Setting;
use App\Models\SharePoint;
use App\Models\User;
use App\Models\MonthlyShareReward;
use App\Models\Wallet;
use App\Models\LeaderboardSetting;
use Illuminate\Support\Facades\DB;

class ShareToEarnService
{
    /**
     * Get points per share action from configurable settings.
     */
    protected function getPointsPerShare(): int
    {
        return LeaderboardSetting::get('share_points_per_share', 10);
    }

    /**
     * Get points per click from configurable settings.
     */
    protected function getPointsPerClick(): int
    {
        return LeaderboardSetting::get('share_points_per_click', 5);
    }

    /**
     * Get points per purchase from configurable settings.
     */
    protected function getPointsPerPurchase(): int
    {
        return LeaderboardSetting::get('share_points_per_purchase', 50);
    }

    /**
     * Check if duplicate share prevention is enabled.
     */
    protected function isDuplicateSharePreventionEnabled(): bool
    {
        return LeaderboardSetting::get('duplicate_share_prevention', true);
    }

    /**
     * Get monthly point cap.
     */
    protected function getMonthlyPointCap(): int
    {
        return LeaderboardSetting::get('leaderboard_monthly_point_cap', 10000);
    }

    /**
     * Award points for sharing a note.
     */
    public function awardSharePoints(User $user, Note $note, ?NoteShareReferral $shareReferral = null): ?SharePoint
    {
        // Check duplicate share prevention
        if ($this->isDuplicateSharePreventionEnabled()) {
            $existing = SharePoint::where('user_id', $user->id)
                ->where('note_id', $note->id)
                ->where('action', 'share')
                ->exists();

            if ($existing) {
                return null; // User has already shared this note
            }
        }

        // Check monthly point cap
        $monthPoints = SharePoint::where('user_id', $user->id)
            ->whereYear('earned_date', now()->year)
            ->whereMonth('earned_date', now()->month)
            ->sum('points');

        $points = $this->getPointsPerShare();
        if ($monthPoints + $points > $this->getMonthlyPointCap()) {
            return null; // Would exceed monthly cap
        }

        return SharePoint::create([
            'user_id' => $user->id,
            'note_id' => $note->id,
            'share_referral_id' => $shareReferral?->id,
            'points' => $points,
            'action' => 'share',
            'earned_date' => now()->toDateString(),
        ]);
    }

    /**
     * Award points for click on share link.
     */
    public function awardClickPoints(NoteShareReferral $shareReferral): ?SharePoint
    {
        $points = $this->getPointsPerClick();

        // Prevent duplicate points for same click (check last hour)
        $oneHourAgo = now()->subHour();
        $existing = SharePoint::where('share_referral_id', $shareReferral->id)
            ->where('action', 'click')
            ->where('created_at', '>=', $oneHourAgo)
            ->first();

        if ($existing) {
            return null;
        }

        // Check monthly point cap
        $monthPoints = SharePoint::where('user_id', $shareReferral->sharer_id)
            ->whereYear('earned_date', now()->year)
            ->whereMonth('earned_date', now()->month)
            ->sum('points');

        if ($monthPoints + $points > $this->getMonthlyPointCap()) {
            return null; // Would exceed monthly cap
        }

        return SharePoint::create([
            'user_id' => $shareReferral->sharer_id,
            'note_id' => $shareReferral->note_id,
            'share_referral_id' => $shareReferral->id,
            'points' => $points,
            'action' => 'click',
            'earned_date' => now()->toDateString(),
        ]);
    }

    /**
     * Award points for purchase through share link.
     */
    public function awardPurchasePoints(NoteShareReferral $shareReferral): ?SharePoint
    {
        $points = $this->getPointsPerPurchase();

        // Check monthly point cap
        $monthPoints = SharePoint::where('user_id', $shareReferral->sharer_id)
            ->whereYear('earned_date', now()->year)
            ->whereMonth('earned_date', now()->month)
            ->sum('points');

        if ($monthPoints + $points > $this->getMonthlyPointCap()) {
            return null; // Would exceed monthly cap
        }

        return SharePoint::create([
            'user_id' => $shareReferral->sharer_id,
            'note_id' => $shareReferral->note_id,
            'share_referral_id' => $shareReferral->id,
            'points' => $points,
            'action' => 'purchase',
            'earned_date' => now()->toDateString(),
        ]);
    }

    /**
     * Get leaderboard for a specific month.
     */
    public function getLeaderboard(?string $month = null, int $limit = 100): array
    {
        $month = $month ?? now()->format('Y-m');

        // Get user IDs with their total points for the month
        $leaderboard = SharePoint::select('user_id', DB::raw('SUM(points) as total_points'))
            ->whereYear('earned_date', substr($month, 0, 4))
            ->whereMonth('earned_date', substr($month, 5, 2))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        // Fetch all users in one query for efficiency
        $users = User::whereIn('id', $leaderboard->pluck('user_id'))
            ->select('id', 'name', 'username', 'avatar')
            ->get()
            ->keyBy('id');

        // Map leaderboard data with user information
        $result = $leaderboard->map(function ($item, $index) use ($users) {
            return [
                'rank' => $index + 1,
                'user' => $users->get($item->user_id),
                'total_points' => (int) $item->total_points,
            ];
        });

        return $result->toArray();
    }

    /**
     * Get all-time leaderboard.
     */
    public function getAllTimeLeaderboard(int $limit = 100): array
    {
        $leaderboard = SharePoint::select('user_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        $users = \App\Models\User::whereIn('id', $leaderboard->pluck('user_id'))
            ->select('id', 'name', 'username', 'avatar')
            ->get()
            ->keyBy('id');

        return $leaderboard->map(function ($item, $index) use ($users) {
            return [
                'rank' => $index + 1,
                'user' => $users->get($item->user_id),
                'total_points' => (int) $item->total_points,
            ];
        })->toArray();
    }

    /**
     * Calculate and distribute monthly rewards.
     */
    public function calculateMonthlyRewards(string $month): void
    {
        $leaderboard = $this->getLeaderboard($month, 100);

        // Get reward settings
        $topRewards = [
            1 => Setting::getSetting('monthly_reward_rank_1', 'marketplace', 100000),
            2 => Setting::getSetting('monthly_reward_rank_2', 'marketplace', 50000),
            3 => Setting::getSetting('monthly_reward_rank_3', 'marketplace', 25000),
        ];

        $top10Reward = Setting::getSetting('monthly_reward_top_10', 'marketplace', 10000);
        $top50Reward = Setting::getSetting('monthly_reward_top_50', 'marketplace', 5000);

        DB::beginTransaction();
        try {
            foreach ($leaderboard as $entry) {
                $rank = $entry['rank'];
                $user = User::find($entry['user']['id']);

                if (!$user) {
                    continue;
                }

                // Determine reward amount
                $rewardAmount = 0;
                if ($rank <= 3 && isset($topRewards[$rank])) {
                    $rewardAmount = $topRewards[$rank];
                } elseif ($rank <= 10) {
                    $rewardAmount = $top10Reward;
                } elseif ($rank <= 50) {
                    $rewardAmount = $top50Reward;
                }

                if ($rewardAmount > 0) {
                    // Create or update monthly reward record
                    $monthlyReward = MonthlyShareReward::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'month' => $month,
                        ],
                        [
                            'total_points' => $entry['total_points'],
                            'rank' => $rank,
                            'reward_amount' => $rewardAmount,
                            'status' => 'pending',
                        ]
                    );

                    // Add reward to user wallet
                    $baseCurrency = config('currency.base_currency', 'IDR');
                    $user->increment('wallet_balance', $rewardAmount);

                    // Sync Wallet model
                    $userWallet = Wallet::firstOrCreate(
                        ['user_id' => $user->id],
                        ['balance' => 0, 'currency' => $baseCurrency]
                    );
                    if ($userWallet->currency !== $baseCurrency) {
                        $userWallet->currency = $baseCurrency;
                    }
                    $userWallet->balance = $user->wallet_balance;
                    $userWallet->save();

                    // Mark reward as paid
                    $monthlyReward->markAsPaid();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Monthly share rewards calculation failed', [
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
