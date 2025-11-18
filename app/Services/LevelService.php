<?php

namespace App\Services;

use App\Models\Level;
use App\Models\User;
use App\Models\UserLevel;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class LevelService
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Check and assign seller level based on criteria.
     */
    public function checkSellerLevel(User $user): ?Level
    {
        $salesCount = $user->transactionsAsSeller()->where('status', 'success')->count();
        $totalRevenue = $user->transactionsAsSeller()->where('status', 'success')->sum('amount');

        // Get all seller levels ordered by level_order (ascending)
        $levels = Level::where('type', 'seller')
            ->where('is_active', true)
            ->orderBy('level_order', 'asc')
            ->get();

        $newLevel = null;

        foreach ($levels as $level) {
            $meetsCriteria = false;

            switch ($level->criteria_type) {
                case 'total_sales':
                    $meetsCriteria = $salesCount >= $level->criteria_value;
                    break;
                case 'total_revenue':
                    $meetsCriteria = $totalRevenue >= $level->criteria_value;
                    break;
            }

            if ($meetsCriteria) {
                // Check if user already has this level
                $existingUserLevel = UserLevel::where('user_id', $user->id)
                    ->where('level_id', $level->id)
                    ->where('type', 'seller')
                    ->first();

                if (!$existingUserLevel) {
                    // Assign new level
                    UserLevel::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'type' => 'seller',
                        ],
                        [
                            'level_id' => $level->id,
                            'achieved_at' => now(),
                            'notes' => $this->getLevelNotes($level, $salesCount, $totalRevenue),
                        ]
                    );

                    $newLevel = $level;
                }
            }
        }

        // Notify user if new level achieved
        if ($newLevel) {
            $this->notificationService->notifyLevelUp($user, $newLevel, 'seller');
        }

        return $newLevel;
    }

    /**
     * Check and assign buyer level based on criteria.
     */
    public function checkBuyerLevel(User $user): ?Level
    {
        $purchaseCount = $user->transactionsAsBuyer()->where('status', 'success')->count();
        $totalSpending = $user->transactionsAsBuyer()->where('status', 'success')->sum('amount');

        // Get all buyer levels ordered by level_order (ascending)
        $levels = Level::where('type', 'buyer')
            ->where('is_active', true)
            ->orderBy('level_order', 'asc')
            ->get();

        $newLevel = null;

        foreach ($levels as $level) {
            $meetsCriteria = false;

            switch ($level->criteria_type) {
                case 'purchase_count':
                    $meetsCriteria = $purchaseCount >= $level->criteria_value;
                    break;
                case 'total_spending':
                    $meetsCriteria = $totalSpending >= $level->criteria_value;
                    break;
            }

            if ($meetsCriteria) {
                // Check if user already has this level
                $existingUserLevel = UserLevel::where('user_id', $user->id)
                    ->where('level_id', $level->id)
                    ->where('type', 'buyer')
                    ->first();

                if (!$existingUserLevel) {
                    // Assign new level
                    UserLevel::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'type' => 'buyer',
                        ],
                        [
                            'level_id' => $level->id,
                            'achieved_at' => now(),
                            'notes' => $this->getLevelNotes($level, $purchaseCount, $totalSpending),
                        ]
                    );

                    $newLevel = $level;
                }
            }
        }

        // Notify user if new level achieved
        if ($newLevel) {
            $this->notificationService->notifyLevelUp($user, $newLevel, 'buyer');
        }

        return $newLevel;
    }

    /**
     * Get commission discount for seller based on their level.
     */
    public function getCommissionDiscount(User $seller): float
    {
        $sellerLevel = $seller->current_seller_level;
        if (!$sellerLevel) {
            return 0;
        }

        return (float) $sellerLevel->commission_discount_percent;
    }

    /**
     * Check if user has priority support.
     */
    public function hasPrioritySupport(User $user, string $type = 'seller'): bool
    {
        $level = $type === 'seller' ? $user->current_seller_level : $user->current_buyer_level;
        return $level && $level->priority_support;
    }

    /**
     * Check if user has early access.
     */
    public function hasEarlyAccess(User $user, string $type = 'seller'): bool
    {
        $level = $type === 'seller' ? $user->current_seller_level : $user->current_buyer_level;
        return $level && $level->early_access;
    }

    /**
     * Get level notes for achievement.
     */
    protected function getLevelNotes(Level $level, $value1, $value2): string
    {
        if ($level->type === 'seller') {
            if ($level->criteria_type === 'total_sales') {
                return "Achieved {$value1} sales";
            } elseif ($level->criteria_type === 'total_revenue') {
                return "Achieved " . currency($value1) . " in revenue";
            }
        } elseif ($level->type === 'buyer') {
            if ($level->criteria_type === 'purchase_count') {
                return "Made {$value1} purchases";
            } elseif ($level->criteria_type === 'total_spending') {
                return "Spent " . currency($value1);
            }
        }

        return "Level achieved";
    }
}

