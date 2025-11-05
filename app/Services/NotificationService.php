<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function create(User $user, string $type, string $title, string $message, ?string $link = null, ?array $data = null): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $data,
        ]);
    }

    /**
     * Notify user about a new purchase.
     */
    public function notifyPurchase(User $user, string $noteTitle, float $amount, string $transactionId): AppNotification
    {
        return $this->create(
            $user,
            'purchase',
            '✅ Purchase Successful!',
            "You successfully purchased: {$noteTitle} for Rp " . number_format($amount, 0, ',', '.'),
            route('notes.show', $transactionId),
            ['transaction_id' => $transactionId]
        );
    }

    /**
     * Notify seller about a new sale.
     */
    public function notifySale(User $seller, string $noteTitle, float $amount, string $buyerName): AppNotification
    {
        return $this->create(
            $seller,
            'sale',
            '💰 New Sale!',
            "{$buyerName} purchased your note: {$noteTitle} for Rp " . number_format($amount, 0, ',', '.'),
            route('notes.index'),
            ['buyer_name' => $buyerName]
        );
    }

    /**
     * Notify user about a review on their note.
     */
    public function notifyReview(User $user, string $noteTitle, int $rating, string $reviewerName): AppNotification
    {
        return $this->create(
            $user,
            'review',
            '⭐ New Review!',
            "{$reviewerName} rated your note '{$noteTitle}' with {$rating} stars.",
            route('notes.index'),
            ['rating' => $rating, 'reviewer' => $reviewerName]
        );
    }

    /**
     * Notify user about ticket response.
     */
    public function notifyTicketResponse(User $user, string $ticketTitle): AppNotification
    {
        return $this->create(
            $user,
            'ticket_response',
            '🎫 Ticket Response',
            "Admin has responded to your support ticket: {$ticketTitle}",
            route('support-tickets.index'),
            ['ticket_title' => $ticketTitle]
        );
    }

    /**
     * Notify user about subscription approval.
     */
    public function notifySubscriptionApproved(User $user, string $plan): AppNotification
    {
        return $this->create(
            $user,
            'subscription',
            '🎉 Subscription Approved!',
            "Your {$plan} subscription has been approved!",
            route('subscription.index'),
            ['plan' => $plan]
        );
    }

    /**
     * Notify user about withdrawal status.
     */
    public function notifyWithdrawal(User $user, string $status, float $amount): AppNotification
    {
        $title = $status === 'approved' ? '✅ Withdrawal Approved!' : '❌ Withdrawal Rejected';
        $message = $status === 'approved' 
            ? "Your withdrawal of Rp " . number_format($amount, 0, ',', '.') . " has been approved."
            : "Your withdrawal request was rejected. Please contact support.";

        return $this->create(
            $user,
            'withdrawal',
            $title,
            $message,
            route('wallet.index'),
            ['amount' => $amount, 'status' => $status]
        );
    }

    /**
     * Notify user about subscription renewal success.
     */
    public function notifySubscriptionRenewed(User $user, float $amount): AppNotification
    {
        return $this->create(
            $user,
            'subscription_renewed',
            '✅ Subscription Renewed',
            "Your premium subscription has been automatically renewed for another month. Rp " . number_format($amount, 0, ',', '.') . " has been deducted from your wallet.",
            route('subscription.index'),
            ['amount' => $amount, 'renewed_at' => now()]
        );
    }

    /**
     * Notify user about subscription expiration due to insufficient balance.
     */
    public function notifySubscriptionExpired(User $user, float $requiredAmount, float $currentBalance): AppNotification
    {
        return $this->create(
            $user,
            'subscription_expired',
            '⚠️ Premium Subscription Expired',
            "Your premium subscription has expired due to insufficient wallet balance (Rp " . number_format($requiredAmount, 0, ',', '.') . " required). Please top up your wallet to reactivate.",
            route('wallet.index'),
            ['required_amount' => $requiredAmount, 'current_balance' => $currentBalance]
        );
    }
}

