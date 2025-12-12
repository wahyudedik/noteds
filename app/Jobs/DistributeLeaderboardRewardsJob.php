<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Wallet;
use App\Models\SharePoint;
use App\Models\Transaction;
use App\Services\CurrencyService;
use Illuminate\Bus\Queueable;
use App\Models\LeaderboardSetting;
use App\Models\MonthlyShareReward;
use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DistributeLeaderboardRewardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;
    protected $currencyService;

    public function __construct($month = null)
    {
        $this->month = $month ?? now()->subMonth()->format('Y-m');
        $this->currencyService = app(CurrencyService::class);
    }

    public function handle()
    {
        if (!LeaderboardSetting::get('auto_transfer_rewards', true)) {
            Log::info('Leaderboard reward auto-transfer is disabled');
            return;
        }

        $leaderboard = $this->getLeaderboard($this->month);

        if (empty($leaderboard)) {
            Log::info("No leaderboard data for month: {$this->month}");
            return;
        }

        $adminUser = User::where('role', 'admin')->where('is_superadmin', true)->first();

        if (!$adminUser) {
            Log::warning('No admin user found for leaderboard reward distribution');
            return;
        }

        $rank1Reward = LeaderboardSetting::get('monthly_reward_rank_1', 100000);
        $rank2Reward = LeaderboardSetting::get('monthly_reward_rank_2', 50000);
        $rank3Reward = LeaderboardSetting::get('monthly_reward_rank_3', 25000);
        $top10Reward = LeaderboardSetting::get('monthly_reward_top_10', 5000);
        $top50Reward = LeaderboardSetting::get('monthly_reward_top_50', 1000);

        $distributedRewards = [];
        $totalAmount = 0;

        DB::transaction(function () use ($leaderboard, $adminUser, $rank1Reward, $rank2Reward, $rank3Reward, $top10Reward, $top50Reward, &$distributedRewards, &$totalAmount) {
            foreach ($leaderboard as $entry) {
                $user = $entry['user'];
                $userId = $user->id;
                $rank = $entry['rank'];
                $reward = 0;

                if ($rank === 1) {
                    $reward = $rank1Reward;
                } elseif ($rank === 2) {
                    $reward = $rank2Reward;
                } elseif ($rank === 3) {
                    $reward = $rank3Reward;
                } elseif ($rank >= 4 && $rank <= 10) {
                    $reward = $top10Reward;
                } elseif ($rank >= 11 && $rank <= 50) {
                    $reward = $top50Reward;
                }

                if ($reward > 0) {
                    // Get user's currency and base currency
                    $userCurrency = $this->currencyService->getUserCurrency($user);
                    $baseCurrency = $this->currencyService->getBaseCurrency();

                    // Convert reward to user's currency
                    $rewardInUserCurrency = $reward;
                    $exchangeRate = 1;
                    if ($userCurrency !== $baseCurrency) {
                        $exchangeRate = $this->currencyService->getExchangeRate($baseCurrency, $userCurrency);
                        $rewardInUserCurrency = $reward * $exchangeRate;
                    }

                    $adminWallet = Wallet::firstOrCreate(
                        ['user_id' => $adminUser->id],
                        ['balance' => 0]
                    );

                    if ($adminWallet->balance >= $reward) {
                        $adminWallet->decrement('balance', $reward);

                        $userWallet = Wallet::firstOrCreate(
                            ['user_id' => $userId],
                            ['balance' => 0, 'currency' => $userCurrency]
                        );
                        if ($userWallet->currency !== $userCurrency) {
                            $userWallet->currency = $userCurrency;
                        }
                        $userWallet->increment('balance', $rewardInUserCurrency);

                        MonthlyShareReward::create([
                            'user_id' => $userId,
                            'month' => $this->month,
                            'rank' => $rank,
                            'points' => $entry['total_points'],
                            'reward_amount' => $rewardInUserCurrency,
                            'transferred_at' => now(),
                        ]);

                        // Create transaction record for audit trail
                        Transaction::create([
                            'user_id' => $userId,
                            'type' => 'leaderboard_reward',
                            'amount' => $rewardInUserCurrency,
                            'currency' => $userCurrency,
                            'original_amount' => $reward,
                            'original_currency' => $baseCurrency,
                            'exchange_rate' => $exchangeRate,
                            'description' => "Leaderboard Monthly Reward - Rank {$rank}",
                        ]);

                        $distributedRewards[$userId] = [
                            'user' => $entry['user'],
                            'rank' => $rank,
                            'amount' => $rewardInUserCurrency,
                        ];
                        $totalAmount += $rewardInUserCurrency;

                        Log::info("Distributed reward to user {$userId}: Rank {$rank}, Amount {$rewardInUserCurrency} {$userCurrency} (Base: {$reward} {$baseCurrency})");
                    } else {
                        Log::warning("Insufficient admin balance for reward distribution. User: {$userId}, Reward: {$reward}");
                    }
                }
            }
        });

        // Send notifications to users and admins
        $this->sendUserNotifications($distributedRewards);
        $this->sendAdminNotifications($distributedRewards, $totalAmount);

        Log::info("Leaderboard rewards distribution completed for month: {$this->month}");
    }

    protected function sendUserNotifications(array $distributedRewards): void
    {
        foreach ($distributedRewards as $userId => $rewardData) {
            AppNotification::create([
                'user_id' => $userId,
                'type' => 'leaderboard_reward',
                'title' => '🎉 Selamat! Anda Mendapat Reward Leaderboard',
                'message' => "Anda menduduki peringkat #{$rewardData['rank']} di leaderboard sharing bulan ini. Wallet Anda telah ditambahkan Rp " . number_format($rewardData['amount']),
                'data' => [
                    'rank' => $rewardData['rank'],
                    'amount' => $rewardData['amount'],
                    'month' => $this->month,
                    'action_url' => '/wallet',
                ],
                'read_at' => null,
            ]);

            Log::info("Notification sent to user {$userId} for rank {$rewardData['rank']}");
        }
    }

    protected function sendAdminNotifications(array $distributedRewards, int $totalAmount): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            // Main distribution notification
            AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'admin_reward_distribution',
                'title' => '✅ Leaderboard Rewards Distributed',
                'message' => "Reward leaderboard sharing bulan {$this->month} telah terdistribusi ke " . count($distributedRewards) . " user dengan total Rp " . number_format($totalAmount),
                'data' => [
                    'month' => $this->month,
                    'recipient_count' => count($distributedRewards),
                    'total_amount' => $totalAmount,
                    'distributed_at' => now()->toIso8601String(),
                ],
                'read_at' => null,
            ]);

            // Rank 1 special notification
            $rank1 = collect($distributedRewards)->firstWhere('rank', 1);
            if ($rank1) {
                AppNotification::create([
                    'user_id' => $admin->id,
                    'type' => 'admin_top_achiever',
                    'title' => '🥇 Top Sharer: ' . $rank1['user']->name,
                    'message' => "{$rank1['user']->name} (@{$rank1['user']->username}) adalah top sharer bulan {$this->month}. Wallet sudah ditambahkan Rp " . number_format($rank1['amount']),
                    'data' => [
                        'rank' => 1,
                        'user_id' => $rank1['user']->id,
                        'user_name' => $rank1['user']->name,
                        'amount' => $rank1['amount'],
                        'month' => $this->month,
                        'action_url' => '/admin/users/' . $rank1['user']->id,
                    ],
                    'read_at' => null,
                ]);
            }

            // Detailed distribution list
            $detailedList = collect($distributedRewards)
                ->sortBy('rank')
                ->map(fn($reward) => "Rank #{$reward['rank']}: {$reward['user']->name} (@{$reward['user']->username}) - Rp " . number_format($reward['amount']))
                ->implode("\n");

            AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'admin_reward_details',
                'title' => 'Detail Distribusi Reward - ' . $this->month,
                'message' => $detailedList,
                'data' => [
                    'month' => $this->month,
                    'distribution_count' => count($distributedRewards),
                ],
                'read_at' => null,
            ]);

            Log::info("Notifications sent to admin {$admin->id}");
        }
    }

    protected function getLeaderboard($month)
    {
        $leaderboard = SharePoint::select('user_id', DB::raw('SUM(points) as total_points'))
            ->whereYear('earned_date', substr($month, 0, 4))
            ->whereMonth('earned_date', substr($month, 5, 2))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        $users = User::whereIn('id', $leaderboard->pluck('user_id'))
            ->select('id', 'name', 'username', 'avatar')
            ->get()
            ->keyBy('id');

        $result = $leaderboard->map(function ($item, $index) use ($users) {
            return [
                'rank' => $index + 1,
                'user' => $users->get($item->user_id),
                'total_points' => (int) $item->total_points,
            ];
        });

        return $result->toArray();
    }
}
