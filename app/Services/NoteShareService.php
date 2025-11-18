<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteShareReferral;
use App\Models\NoteSharePurchase;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class NoteShareService
{
    public function __construct(
        private NotificationService $notificationService,
        private ShareToEarnService $shareToEarnService
    ) {
    }

    /**
     * Get share commission percentage from settings.
     */
    public function getShareCommissionPercent(): float
    {
        return Setting::getSetting('share_commission_percent', 'marketplace', 5.0);
    }

    /**
     * Get or create a share referral for a note and user.
     */
    public function getOrCreateShareReferral(Note $note, User $sharer): NoteShareReferral
    {
        return NoteShareReferral::firstOrCreate(
            [
                'note_id' => $note->id,
                'sharer_id' => $sharer->id,
            ],
            [
                'referral_token' => NoteShareReferral::generateToken(),
            ]
        );
    }

    /**
     * Generate share URL for a note.
     */
    public function generateShareUrl(Note $note, User $sharer): string
    {
        $shareReferral = $this->getOrCreateShareReferral($note, $sharer);
        
        // Award share-to-earn points (for leaderboard)
        $this->shareToEarnService->awardSharePoints($sharer, $note, $shareReferral);
        
        // Award regular points (for redemption system)
        try {
            $pointsService = app(\App\Services\PointsService::class);
            $pointsService->awardSharePoints($sharer, $note);
        } catch (\Exception $e) {
            logger()->error('Failed to award share points', [
                'user_id' => $sharer->id,
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $shareReferral->share_url;
    }

    /**
     * Track a click on a share referral link.
     */
    public function trackClick(string $referralToken): ?NoteShareReferral
    {
        $shareReferral = NoteShareReferral::where('referral_token', $referralToken)->first();
        
        if ($shareReferral) {
            $shareReferral->trackClick();
            
            // Award points for click
            $this->shareToEarnService->awardClickPoints($shareReferral);
            
            return $shareReferral;
        }

        return null;
    }

    /**
     * Process commission for a purchase made through a share referral.
     */
    public function processShareCommission(Transaction $transaction, ?string $referralToken = null): ?NoteSharePurchase
    {
        // If no referral token provided, check if transaction has one stored
        if (!$referralToken) {
            // Transaction notes might be JSON string or array
            $notes = $transaction->notes;
            if (is_string($notes)) {
                $notes = json_decode($notes, true) ?? [];
            }
            $referralToken = $notes['share_referral_token'] ?? null;
        }

        if (!$referralToken) {
            return null;
        }

        $shareReferral = NoteShareReferral::where('referral_token', $referralToken)
            ->where('note_id', $transaction->note_id)
            ->first();

        if (!$shareReferral) {
            return null;
        }

        // Don't give commission if sharer is the buyer or seller
        if ($shareReferral->sharer_id === $transaction->buyer_id || 
            $shareReferral->sharer_id === $transaction->seller_id) {
            return null;
        }

        // Calculate commission
        $commissionPercent = $this->getShareCommissionPercent();
        $commissionAmount = $transaction->amount * ($commissionPercent / 100);

        if ($commissionAmount <= 0) {
            return null;
        }

        try {
            DB::beginTransaction();

            // Record the purchase
            $sharePurchase = $shareReferral->recordPurchase(
                $transaction->amount,
                $commissionAmount,
                $transaction->id
            );

            // Add commission to sharer's wallet
            $sharer = $shareReferral->sharer;
            $baseCurrency = config('currency.base_currency', 'IDR');
            
            $sharer->increment('wallet_balance', $commissionAmount);

            // Sync Wallet model
            $sharerWallet = Wallet::firstOrCreate(
                ['user_id' => $sharer->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($sharerWallet->currency !== $baseCurrency) {
                $sharerWallet->currency = $baseCurrency;
            }
            $sharerWallet->balance = $sharer->wallet_balance;
            $sharerWallet->save();

                // Mark commission as paid
                $sharePurchase->markAsPaid();

                // Award points for purchase
                $this->shareToEarnService->awardPurchasePoints($shareReferral);

                DB::commit();

                // Send notification
                $this->notificationService->notifyShareCommission(
                    $sharer,
                    $shareReferral->note,
                    $commissionAmount,
                    $commissionPercent
                );

                return $sharePurchase;
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Share commission processing failed', [
                'share_referral_id' => $shareReferral->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get share statistics for a user.
     */
    public function getUserShareStats(User $user): array
    {
        $shareReferrals = NoteShareReferral::where('sharer_id', $user->id)
            ->with('note:id,title,price,user_id')
            ->get();

        return [
            'total_shares' => $shareReferrals->count(),
            'total_clicks' => $shareReferrals->sum('click_count'),
            'total_purchases' => $shareReferrals->sum('purchase_count'),
            'total_commission_earned' => $shareReferrals->sum('total_commission_earned'),
            'total_revenue_generated' => $shareReferrals->sum('total_revenue_generated'),
            'share_referrals' => $shareReferrals,
        ];
    }

    /**
     * Get share statistics for a note.
     */
    public function getNoteShareStats(Note $note): array
    {
        $shareReferrals = NoteShareReferral::where('note_id', $note->id)
            ->with('sharer:id,name,username,avatar')
            ->get();

        return [
            'total_shares' => $shareReferrals->count(),
            'total_clicks' => $shareReferrals->sum('click_count'),
            'total_purchases' => $shareReferrals->sum('purchase_count'),
            'total_revenue_generated' => $shareReferrals->sum('total_revenue_generated'),
            'share_referrals' => $shareReferrals,
        ];
    }
}

