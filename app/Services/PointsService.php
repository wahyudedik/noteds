<?php

namespace App\Services;

use App\Models\Point;
use App\Models\PointRedemption;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Award points to a user for an action.
     */
    public function awardPoints(
        User $user,
        string $action,
        int $points,
        ?string $description = null,
        ?string $sourceType = null,
        ?string $sourceId = null,
        ?\DateTimeInterface $expiresAt = null
    ): Point {
        // Get expiration days from settings if not provided
        if (!$expiresAt) {
            $expirationDays = Setting::getSetting('points_expiration_days', 'points', 365);
            if ($expirationDays > 0) {
                $expiresAt = now()->addDays($expirationDays);
            }
        }

        return Point::create([
            'user_id' => $user->id,
            'points' => $points,
            'action' => $action,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'description' => $description ?? $this->getDefaultDescription($action),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Get points for a specific action from settings.
     */
    public function getPointsForAction(string $action): int
    {
        $settingKey = 'points_' . str_replace('-', '_', $action);
        return (int) Setting::getSetting($settingKey, 'points', 0);
    }

    /**
     * Award points for purchase.
     */
    public function awardPurchasePoints(User $user, $transaction, int $customPoints = null): ?Point
    {
        $points = $customPoints ?? $this->getPointsForAction('purchase');
        if ($points <= 0) {
            return null;
        }

        // Calculate points based on purchase amount (optional multiplier)
        $multiplier = Setting::getSetting('points_purchase_multiplier', 'points', 1);
        $finalPoints = (int) ($points * $multiplier);

        return $this->awardPoints(
            $user,
            'purchase',
            $finalPoints,
            "Points earned from purchase: " . currency($transaction->amount),
            'transaction',
            $transaction->id
        );
    }

    /**
     * Award points for review.
     */
    public function awardReviewPoints(User $user, $review): ?Point
    {
        $points = $this->getPointsForAction('review');
        if ($points <= 0) {
            return null;
        }

        return $this->awardPoints(
            $user,
            'review',
            $points,
            "Points earned for reviewing a note",
            'review',
            $review->id
        );
    }

    /**
     * Award points for sharing (different from share-to-earn points).
     */
    public function awardSharePoints(User $user, $note): ?Point
    {
        $points = $this->getPointsForAction('share');
        if ($points <= 0) {
            return null;
        }

        return $this->awardPoints(
            $user,
            'share',
            $points,
            "Points earned for sharing a note",
            'note',
            $note->id
        );
    }

    /**
     * Redeem points for discount.
     */
    public function redeemForDiscount(
        User $user,
        int $pointsRequired,
        float $discountAmount = null,
        float $discountPercent = null,
        int $validDays = 30
    ): PointRedemption {
        // Validate user has enough points
        if ($user->total_points < $pointsRequired) {
            throw new \Exception('Insufficient points for redemption.');
        }

        DB::beginTransaction();
        try {
            // Mark points as redeemed (FIFO - oldest first, excluding expired)
            $pointsToRedeem = Point::where('user_id', $user->id)
                ->where('is_redeemed', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $pointsUsed = 0;
            $redemption = PointRedemption::create([
                'user_id' => $user->id,
                'redemption_type' => 'discount',
                'redemption_code' => PointRedemption::generateCode(),
                'points_used' => $pointsRequired,
                'discount_amount' => $discountAmount,
                'discount_percent' => $discountPercent,
                'description' => $discountAmount 
                    ? "Discount of " . currency($discountAmount) 
                    : "Discount of {$discountPercent}%",
                'status' => 'active',
                'expires_at' => now()->addDays($validDays),
            ]);

            foreach ($pointsToRedeem as $point) {
                if ($pointsUsed >= $pointsRequired) {
                    break;
                }

                $pointsToUse = min($point->points, $pointsRequired - $pointsUsed);
                if ($pointsToUse < $point->points) {
                    // Split point record if needed
                    $remainingPoints = $point->points - $pointsToUse;
                    Point::create([
                        'user_id' => $user->id,
                        'points' => $remainingPoints,
                        'action' => $point->action,
                        'source_type' => $point->source_type,
                        'source_id' => $point->source_id,
                        'description' => $point->description,
                        'expires_at' => $point->expires_at,
                    ]);
                }

                $point->update([
                    'is_redeemed' => true,
                    'redemption_id' => $redemption->id,
                ]);

                $pointsUsed += $pointsToUse;
            }

            DB::commit();
            return $redemption;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Redeem points for premium features.
     */
    public function redeemForPremium(User $user, int $pointsRequired, int $premiumDays): PointRedemption
    {
        if ($user->total_points < $pointsRequired) {
            throw new \Exception('Insufficient points for redemption.');
        }

        DB::beginTransaction();
        try {
            $pointsToRedeem = Point::where('user_id', $user->id)
                ->where('is_redeemed', false)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $pointsUsed = 0;
            $redemption = PointRedemption::create([
                'user_id' => $user->id,
                'redemption_type' => 'premium_feature',
                'points_used' => $pointsRequired,
                'premium_days' => $premiumDays,
                'description' => "Premium access for {$premiumDays} days",
                'status' => 'active',
            ]);

            foreach ($pointsToRedeem as $point) {
                if ($pointsUsed >= $pointsRequired) {
                    break;
                }

                $pointsToUse = min($point->points, $pointsRequired - $pointsUsed);
                if ($pointsToUse < $point->points) {
                    $remainingPoints = $point->points - $pointsToUse;
                    Point::create([
                        'user_id' => $user->id,
                        'points' => $remainingPoints,
                        'action' => $point->action,
                        'source_type' => $point->source_type,
                        'source_id' => $point->source_id,
                        'description' => $point->description,
                        'expires_at' => $point->expires_at,
                    ]);
                }

                $point->update([
                    'is_redeemed' => true,
                    'redemption_id' => $redemption->id,
                ]);

                $pointsUsed += $pointsToUse;
            }

            // Apply premium to user (if premium system exists)
            // Note: Premium feature has been removed, but keeping for backward compatibility
            // The redemption status will be 'active' and can be used in future features
            // For now, we just record the redemption without applying premium
            $user->save();

            DB::commit();
            return $redemption;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process expired points (mark as expired, can be called by scheduler).
     */
    public function processExpiredPoints(): int
    {
        $expiredCount = Point::where('is_redeemed', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        // Optionally log or notify users about expired points
        // For now, we just count them (they're already filtered out in total_points calculation)

        return $expiredCount;
    }

    /**
     * Get default description for action.
     */
    protected function getDefaultDescription(string $action): string
    {
        $descriptions = [
            'purchase' => 'Points earned from purchase',
            'review' => 'Points earned for review',
            'share' => 'Points earned for sharing',
            'daily_login' => 'Points earned for daily login',
            'referral' => 'Points earned from referral',
        ];

        return $descriptions[$action] ?? "Points earned from {$action}";
    }
}

