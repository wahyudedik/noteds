<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForumNotificationMail;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function create(User $user, string $type, string $title, string $message, ?string $link = null, ?array $data = null): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $data,
        ]);

        $this->sendForumEmailIfEnabled($user, $type, $title, $message, $link);

        return $notification;
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

    /**
     * Notify user about a like on their post.
     */
    public function notifyPostLiked(User $user, string $postId, string $likerName, string $postContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_post_liked',
            '❤️ Your post was liked',
            "{$likerName} liked your post: " . Str::limit($postContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'liker_name' => $likerName]
        );
    }

    /**
     * Notify user about a comment on their post.
     */
    public function notifyPostCommented(User $user, string $postId, string $commenterName, string $commentContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_post_commented',
            '💬 New comment on your post',
            "{$commenterName} commented on your post: " . Str::limit($commentContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'commenter_name' => $commenterName]
        );
    }

    /**
     * Notify user about a reply to their comment.
     */
    public function notifyCommentReplied(User $user, string $postId, string $replierName, string $replyContent): AppNotification
    {
        return $this->create(
            $user,
            'forum_comment_replied',
            '💬 Reply to your comment',
            "{$replierName} replied to your comment: " . Str::limit($replyContent, 50),
            route('forum.show', $postId),
            ['post_id' => $postId, 'replier_name' => $replierName]
        );
    }

    /**
     * Notify user about a like on their comment.
     */
    public function notifyCommentLiked(User $user, string $postId, string $likerName): AppNotification
    {
        return $this->create(
            $user,
            'forum_comment_liked',
            '❤️ Your comment was liked',
            "{$likerName} liked your comment",
            route('forum.show', $postId),
            ['post_id' => $postId, 'liker_name' => $likerName]
        );
    }

    /**
     * Notify user about a new follower.
     */
    public function notifyNewFollower(User $user, string $followerName): AppNotification
    {
        // Get follower user to get their username
        $follower = User::where('name', $followerName)->first();
        $followerUsername = $follower ? $follower->username : null;
        
        return $this->create(
            $user,
            'forum_new_follower',
            '👤 New follower',
            "{$followerName} started following you",
            $followerUsername ? route('public.profile.show', $followerUsername) : null,
            ['follower_name' => $followerName]
        );
    }

    protected function sendForumEmailIfEnabled(User $user, string $type, string $title, string $message, ?string $link = null): void
    {
        if (!Str::startsWith($type, 'forum_')) {
            return;
        }

        if (!$user->wantsForumEmail($type)) {
            return;
        }

        if (empty($user->email)) {
            return;
        }

        Mail::to($user->email)->queue(new ForumNotificationMail($title, $message, $link));
    }
}

