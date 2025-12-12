<?php

namespace App\Services;

use App\Models\AffiliateLink;
use App\Models\AffiliateConversion;
use App\Services\CurrencyService;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateCommissionTier;
use App\Models\Transaction;
use App\Models\PurchasedNote;
use App\Models\User;
use App\Models\Setting;
use App\Events\AffiliateConversionCompleted;
use App\Events\AffiliatePayoutRequested;
use App\Mail\AffiliateConversionMail;
use App\Mail\AffiliatePayoutRequestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AffiliateService
{
    /**
     * Get commission rate by tier for an affiliate.
     * Uses dynamic commission tiers if available, otherwise falls back to default rates.
     */
    public function getCommissionRate(User $affiliate, int $tier = 1): float
    {
        // Get affiliate's performance stats
        $conversions = $affiliate->affiliateConversions()->count();
        $revenue = $affiliate->affiliateConversions()->sum('transaction_amount');

        // Find the best matching commission tier
        $commissionTier = AffiliateCommissionTier::active()
            ->ordered()
            ->get()
            ->filter(function ($tierModel) use ($conversions, $revenue) {
                return $tierModel->qualifies($conversions, $revenue);
            })
            ->last();

        if ($commissionTier) {
            return $commissionTier->getCommissionRate($tier);
        }

        // Fallback to default rates
        $rates = [
            1 => 10.0,
            2 => 5.0,
            3 => 2.0,
        ];

        return $rates[$tier] ?? 0;
    }

    /**
     * Get commission rate by tier (backward compatibility).
     * @deprecated Use getCommissionRate(User $affiliate, int $tier) instead
     */
    public function getCommissionRateLegacy(int $tier = 1): float
    {
        $rates = [
            1 => 10.0,
            2 => 5.0,
            3 => 2.0,
        ];

        return $rates[$tier] ?? 0;
    }

    /**
     * Get max tier for multi-tier affiliate.
     */
    public function getMaxTier(): int
    {
        return Setting::getSetting('affiliate_max_tier', 'affiliate', 3);
    }

    /**
     * Generate unique affiliate link for user.
     */
    public function generateAffiliateLink(User $user, ?string $name = null, ?string $description = null, ?string $destinationUrl = null): AffiliateLink
    {
        return AffiliateLink::create([
            'affiliate_id' => $user->id,
            'code' => AffiliateLink::generateUniqueCode(),
            'name' => $name,
            'description' => $description,
            'destination_url' => $destinationUrl,
            'is_active' => true,
        ]);
    }

    /**
     * Track affiliate link click.
     */
    public function trackClick(string $code, ?string $ipAddress = null, ?string $userAgent = null): ?AffiliateLink
    {
        $link = AffiliateLink::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$link) {
            return null;
        }

        $link->increment('clicks');

        // Store affiliate code in cookie for 30 days
        Cookie::queue('affiliate_code', $code, 60 * 24 * 30);

        // Also store in session as backup
        session(['affiliate_code' => $code]);

        return $link;
    }

    /**
     * Track conversion (signup or purchase).
     */
    public function trackConversion(
        User $converter,
        string $conversionType = 'purchase',
        ?Transaction $transaction = null,
        ?PurchasedNote $purchase = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ?AffiliateConversion {
        // Get affiliate code from cookie or session
        $affiliateCode = request()->cookie('affiliate_code') ?? session('affiliate_code');

        // Also check if converter was referred (for multi-tier)
        if (!$affiliateCode && $converter->referred_by) {
            // Find affiliate link from referrer
            $referrer = User::find($converter->referred_by);
            if ($referrer) {
                $affiliateLink = AffiliateLink::where('affiliate_id', $referrer->id)
                    ->where('is_active', true)
                    ->first();
                if ($affiliateLink) {
                    $affiliateCode = $affiliateLink->code;
                }
            }
        }

        if (!$affiliateCode) {
            return null;
        }

        $link = AffiliateLink::where('code', $affiliateCode)
            ->where('is_active', true)
            ->first();

        if (!$link) {
            return null;
        }

        try {
            DB::beginTransaction();

            $conversion = AffiliateConversion::create([
                'affiliate_link_id' => $link->id,
                'affiliate_id' => $link->affiliate_id,
                'converter_id' => $converter->id,
                'transaction_id' => $transaction?->id,
                'purchase_id' => $purchase?->id,
                'conversion_type' => $conversionType,
                'transaction_amount' => $transaction?->amount ?? $purchase?->purchase_price ?? 0,
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'clicked_at' => now()->subMinutes(rand(1, 60)), // Simulate click time
                'converted_at' => now(),
            ]);

            $link->increment('conversions');

            // Create commissions for multi-tier
            $this->createCommissions($conversion, $link->affiliate, $transaction ?? $purchase, 1);

            DB::commit();

            // Get tier 1 commission for notification
            $tier1Commission = $conversion->commissions()->where('tier', 1)->first();
            $commissionAmount = $tier1Commission?->commission_amount ?? 0;

            // Dispatch conversion event for broadcasting
            broadcast(new AffiliateConversionCompleted($conversion, $commissionAmount, 1));

            // Queue notification email to affiliate
            Mail::queue(new AffiliateConversionMail($conversion, $commissionAmount, 1));

            return $conversion;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Affiliate conversion tracking failed', [
                'error' => $e->getMessage(),
                'affiliate_code' => $affiliateCode,
                'converter_id' => $converter->id,
            ]);

            return null;
        }
    }

    /**
     * Create commissions for multi-tier affiliate.
     */
    protected function createCommissions(
        AffiliateConversion $conversion,
        User $affiliate,
        Transaction|PurchasedNote|null $source,
        int $tier
    ): void {
        if ($tier > $this->getMaxTier()) {
            return;
        }

        $transactionAmount = $source instanceof Transaction
            ? $source->amount
            : ($source instanceof PurchasedNote ? $source->purchase_price : 0);

        if ($transactionAmount <= 0) {
            return;
        }

        $commissionRate = $this->getCommissionRate($affiliate, $tier);
        if ($commissionRate <= 0) {
            return;
        }

        $commissionAmount = $transactionAmount * ($commissionRate / 100);

        // Get parent affiliate (the one who referred this affiliate)
        $parentAffiliate = $tier > 1 ? $this->getParentAffiliate($affiliate, $tier) : null;

        // Create commission
        $commission = AffiliateCommission::create([
            'affiliate_id' => $affiliate->id,
            'conversion_id' => $conversion->id,
            'transaction_id' => $source instanceof Transaction ? $source->id : null,
            'tier' => $tier,
            'parent_affiliate_id' => $parentAffiliate?->id,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'transaction_amount' => $transactionAmount,
            'status' => 'pending',
        ]);

        // Update affiliate link total commission (only for tier 1)
        $affiliateLink = $conversion->affiliateLink;
        if ($affiliateLink && $tier === 1) {
            $affiliateLink->increment('total_commission', $commissionAmount);
        }

        // Create commission for next tier (if parent affiliate exists)
        if ($parentAffiliate && $tier < $this->getMaxTier()) {
            $this->createCommissions($conversion, $parentAffiliate, $source, $tier + 1);
        }
    }

    /**
     * Get parent affiliate for tier (based on referral chain).
     * Tier 1 = direct affiliate (no parent)
     * Tier 2 = parent of tier 1 affiliate (who referred tier 1)
     * Tier 3 = parent of tier 2 affiliate (who referred tier 2)
     */
    protected function getParentAffiliate(User $affiliate, int $targetTier): ?User
    {
        // Get parent through referral chain
        // For tier 2, we need the one who referred tier 1 affiliate
        // For tier 3, we need the one who referred tier 2 affiliate
        $current = $affiliate;
        $steps = $targetTier - 1; // Steps to go up the referral chain

        for ($i = 0; $i < $steps; $i++) {
            if (!$current->referred_by) {
                return null;
            }
            $current = User::find($current->referred_by);
            if (!$current) {
                return null;
            }
        }

        return $current;
    }

    /**
     * Get affiliate statistics for user.
     */
    public function getAffiliateStats(User $affiliate): array
    {
        $totalClicks = $affiliate->affiliateLinks()->sum('clicks');
        $totalConversions = $affiliate->affiliateConversions()->count();
        $totalCommissions = $affiliate->affiliateCommissions()->sum('commission_amount');
        $pendingCommissions = $affiliate->affiliateCommissions()->where('status', 'pending')->sum('commission_amount');
        $approvedCommissions = $affiliate->affiliateCommissions()->where('status', 'approved')->sum('commission_amount');
        $paidCommissions = $affiliate->affiliateCommissions()->where('status', 'paid')->sum('commission_amount');
        $totalPayouts = $affiliate->affiliatePayouts()->where('status', 'completed')->sum('amount');
        $pendingPayouts = $affiliate->affiliatePayouts()->whereIn('status', ['pending', 'processing'])->sum('amount');

        $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;

        return [
            'total_links' => $affiliate->affiliateLinks()->count(),
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => round($conversionRate, 2),
            'total_commissions' => $totalCommissions,
            'pending_commissions' => $pendingCommissions,
            'approved_commissions' => $approvedCommissions,
            'paid_commissions' => $paidCommissions,
            'total_payouts' => $totalPayouts,
            'pending_payouts' => $pendingPayouts,
            'available_balance' => $approvedCommissions - $totalPayouts - $pendingPayouts,
        ];
    }

    /**
     * Create payout request for affiliate.
     */
    public function createPayoutRequest(User $affiliate, float $amount, string $payoutMethod = 'wallet', ?array $payoutDetails = null): ?AffiliatePayout
    {
        // Get currency service for conversion
        $currencyService = app(CurrencyService::class);
        $userCurrency = $currencyService->getUserCurrency($affiliate);
        $baseCurrency = $currencyService->getBaseCurrency();

        // Get available balance
        $stats = $this->getAffiliateStats($affiliate);
        $availableBalance = $stats['available_balance'];

        if ($amount > $availableBalance) {
            throw new \Exception('Amount exceeds available balance');
        }

        // Get pending commissions to include
        $commissions = $affiliate->affiliateCommissions()
            ->where('status', 'approved')
            ->whereNull('payout_id')
            ->orderBy('created_at')
            ->get();

        $totalCommissions = $commissions->sum('commission_amount');

        if ($amount > $totalCommissions) {
            throw new \Exception('Amount exceeds available approved commissions');
        }

        try {
            DB::beginTransaction();

            // Calculate exchange rate if user's currency differs from base
            $exchangeRate = 1;
            $amountInBase = $amount;
            if ($userCurrency !== $baseCurrency) {
                $exchangeRate = $currencyService->getExchangeRate($baseCurrency, $userCurrency);
                $amountInBase = $amount / $exchangeRate; // Convert user currency amount back to base
            }

            $payout = AffiliatePayout::create([
                'affiliate_id' => $affiliate->id,
                'amount' => $amount,
                'currency' => $userCurrency,
                'original_amount' => $amountInBase,
                'original_currency' => $baseCurrency,
                'exchange_rate' => $exchangeRate,
                'status' => 'pending',
                'payout_method' => $payoutMethod,
                'payout_details' => $payoutDetails,
                'commission_count' => 0,
            ]);

            // Link commissions to payout (up to requested amount)
            $accumulated = 0;
            foreach ($commissions as $commission) {
                if ($accumulated >= $amount) {
                    break;
                }

                $commission->update([
                    'payout_id' => $payout->id,
                ]);

                $accumulated += $commission->commission_amount;
                $payout->increment('commission_count');
            }

            DB::commit();

            // Dispatch payout requested event to notify admins
            broadcast(new AffiliatePayoutRequested($payout, $affiliate->username, $affiliate->email));

            // Queue notification email to admin
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                Mail::queue(new AffiliatePayoutRequestMail($payout, $affiliate));
            }

            return $payout;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Affiliate payout creation failed', [
                'error' => $e->getMessage(),
                'affiliate_id' => $affiliate->id,
                'amount' => $amount,
            ]);

            throw $e;
        }
    }
}
