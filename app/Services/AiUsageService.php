<?php

namespace App\Services;

use App\Models\AiFeatureUsage;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    public const FEATURE_IMAGE_SEARCH = 'image_search';
    public const FEATURE_IMAGE_GENERATE = 'image_generate';
    public const FEATURE_VIDEO_GENERATE = 'video_generate';

    public function __construct(
        protected CurrencyService $currencyService
    ) {}

    /**
     * Check whether the user can access the feature and if payment is required.
     *
     * @return array{
     *     allowed: bool,
     *     is_paid: bool,
     *     amount: float,
     *     currency: string,
     *     usage_summary: array,
     *     wallet_balance?: float,
     *     message?: string
     * }
     */
    public function checkAvailability(User $user, string $feature): array
    {
        $summary = $this->getUsageSummary($user);
        $limit = $summary['limit'];

        // Unlimited (-1) or free quota available
        if ($limit === -1 || $summary['used_today'] < $limit) {
            return [
                'allowed' => true,
                'is_paid' => false,
                'amount' => 0.0,
                'currency' => $summary['currency'],
                'usage_summary' => $summary,
            ];
        }

        return $this->buildPaidDecision($user, $feature, $summary);
    }

    /**
     * Persist usage record and handle wallet deduction when required.
     *
     * @param array $decision Result from checkAvailability
     * @param array<string, mixed> $metadata
     *
     * @return array{
     *     charged: float,
     *     currency: string,
     *     wallet_balance: float
     * }
     */
    public function recordUsage(User $user, string $feature, array $decision, bool $success, array $metadata = []): array
    {
        $currency = $decision['currency'] ?? $this->currencyService->getBaseCurrency();

        $result = DB::transaction(function () use ($user, $feature, $decision, $success, $metadata, $currency) {
            $amountToCharge = ($success && ($decision['is_paid'] ?? false)) ? (float) ($decision['amount'] ?? 0) : 0.0;
            $chargedAmount = 0.0;

            if ($amountToCharge > 0) {
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

                if (! $wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => $user->wallet_balance ?? 0,
                        'currency' => $currency,
                    ]);
                }

                if ($wallet->currency !== $currency) {
                    $wallet->currency = $currency;
                }

                $wallet->balance = max(0, (float) $wallet->balance - $amountToCharge);
                $wallet->save();

                $user->wallet_balance = $wallet->balance;
                $user->save();

                $chargedAmount = $amountToCharge;
            }

            AiFeatureUsage::create([
                'user_id' => $user->id,
                'feature' => $feature,
                'is_paid' => $chargedAmount > 0,
                'amount' => $chargedAmount > 0 ? $chargedAmount : 0,
                'currency' => $currency,
                'status' => $success ? 'success' : 'failed',
                'metadata' => ! empty($metadata) ? $metadata : null,
            ]);

            return [
                'charged' => $chargedAmount,
                'currency' => $currency,
                'wallet_balance' => $user->wallet_balance ?? 0,
            ];
        });

        return $result;
    }

    /**
     * Get usage summary for the current day.
     *
     * @return array{
     *     limit: int,
     *     used_today: int,
     *     remaining_free: int,
     *     currency: string
     * }
     */
    public function getUsageSummary(User $user): array
    {
        $limit = Setting::getAiFreeUsageLimit();
        $usedToday = AiFeatureUsage::where('user_id', $user->id)
            ->where('status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $currency = $this->currencyService->getBaseCurrency();
        $remaining = $limit === -1 ? -1 : max($limit - $usedToday, 0);

        return [
            'limit' => $limit,
            'used_today' => $usedToday,
            'remaining_free' => $remaining,
            'currency' => $currency,
        ];
    }

    /**
     * Build a paid decision response.
     *
     * @param array $summary
     *
     * @return array{
     *     allowed: bool,
     *     is_paid: bool,
     *     amount: float,
     *     currency: string,
     *     usage_summary: array,
     *     wallet_balance?: float,
     *     message?: string
     * }
     */
    protected function buildPaidDecision(User $user, string $feature, array $summary): array
    {
        $price = Setting::getAiFeaturePrice($feature);
        $currency = $summary['currency'] ?? $this->currencyService->getBaseCurrency();

        if ($price <= 0) {
            return [
                'allowed' => true,
                'is_paid' => false,
                'amount' => 0.0,
                'currency' => $currency,
                'usage_summary' => $summary,
            ];
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => $user->wallet_balance ?? 0,
                'currency' => $currency,
            ]
        );

        if ($wallet->currency !== $currency) {
            $wallet->currency = $currency;
            $wallet->save();
        }

        $allowed = (float) $wallet->balance >= $price;

        return [
            'allowed' => $allowed,
            'is_paid' => true,
            'amount' => $price,
            'currency' => $currency,
            'usage_summary' => $summary,
            'wallet_balance' => (float) $wallet->balance,
            'message' => $allowed ? null : 'Saldo wallet kamu tidak mencukupi untuk menggunakan fitur AI ini.',
        ];
    }
}

