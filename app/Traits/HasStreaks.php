<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasStreaks
{
    /**
     * Get last activity date
     */
    public function lastActivityDate(): ?Carbon
    {
        return $this->last_activity_date ? Carbon::parse($this->last_activity_date) : null;
    }

    /**
     * Update last activity date
     */
    public function updateLastActivity(): void
    {
        $this->last_activity_date = today();
        $this->save();
    }

    /**
     * Get current streak days
     */
    public function getCurrentStreak(): int
    {
        return $this->current_streak ?? 0;
    }

    /**
     * Check if streak is active (logged in yesterday or today)
     */
    public function hasActiveStreak(): bool
    {
        if (!$this->lastActivityDate()) {
            return false;
        }

        $daysSinceLastActivity = today()->diffInDays($this->lastActivityDate());
        return $daysSinceLastActivity <= 1;
    }

    /**
     * Increment streak
     */
    public function incrementStreak(): void
    {
        $this->current_streak = ($this->current_streak ?? 0) + 1;
        $this->last_activity_date = today();
        $this->save();
    }

    /**
     * Reset streak
     */
    public function resetStreak(): void
    {
        $this->current_streak = 0;
        $this->last_activity_date = today();
        $this->save();
    }

    /**
     * Award badge
     */
    public function awardBadge(string $badgeCode): void
    {
        $badge = \App\Models\Badge::where('code', $badgeCode)->first();

        if ($badge && !$this->hasBadge($badgeCode)) {
            \DB::table('user_badges')->insert([
                'user_id' => $this->id,
                'badge_id' => $badge->id,
                'awarded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Check if user has badge
     */
    public function hasBadge(string $badgeCode): bool
    {
        return \DB::table('user_badges')
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->where('user_badges.user_id', $this->id)
            ->where('badges.code', $badgeCode)
            ->exists();
    }

    /**
     * Check if user has claimed a bonus
     */
    public function hasClaimed(string $bonusType): bool
    {
        return \DB::table('streak_rewards')
            ->where('user_id', $this->id)
            ->where('metadata->bonus_type', $bonusType)
            ->exists();
    }

    /**
     * Add points to user
     */
    public function addPoints(int $points, string $reason): void
    {
        \DB::table('points')->insert([
            'user_id' => $this->id,
            'amount' => $points,
            'type' => 'earn',
            'description' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update total points cache
        $this->total_points = ($this->total_points ?? 0) + $points;
        $this->save();
    }
}
