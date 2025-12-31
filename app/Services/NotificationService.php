<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notify seller about new order.
     */
    public function notifyNewOrder(Order $order): void
    {
        $seller = $order->product->seller;
        
        $seller->notify(new \App\Notifications\NewOrderNotification($order));
    }

    /**
     * Notify admin about withdrawal request.
     */
    public function notifyWithdrawalRequest(Withdrawal $withdrawal): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\WithdrawalRequestNotification($withdrawal));
        }
    }

    /**
     * Notify user about withdrawal status.
     */
    public function notifyWithdrawalStatus(Withdrawal $withdrawal): void
    {
        $withdrawal->user->notify(new \App\Notifications\WithdrawalStatusNotification($withdrawal));
    }

    /**
     * Notify brand about new campaign available.
     */
    public function notifyNewCampaign(\App\Models\Campaign $campaign): void
    {
        // Notify clippers about new campaign
        $clippers = \App\Models\User::where('clipper_role', 'clipper')->get();
        
        foreach ($clippers as $clipper) {
            $clipper->notify(new \App\Notifications\NewCampaignNotification($campaign));
        }
    }

    /**
     * Notify clipper about clip approval.
     */
    public function notifyClipApproved(\App\Models\Clip $clip): void
    {
        $clip->clipper->notify(new \App\Notifications\ClipApprovedNotification($clip));
    }

    /**
     * Notify clipper about reward received.
     */
    public function notifyRewardReceived(\App\Models\Clip $clip): void
    {
        $clip->clipper->notify(new \App\Notifications\RewardReceivedNotification($clip));
    }

    /**
     * Notify brand about campaign ended.
     */
    public function notifyCampaignEnded(\App\Models\Campaign $campaign): void
    {
        $campaign->creator->notify(new \App\Notifications\CampaignEndedNotification($campaign));
    }

    /**
     * Notify brand about registration approval.
     */
    public function notifyBrandApproved(\App\Models\User $user): void
    {
        $user->notify(new \App\Notifications\BrandApprovedNotification($user));
    }

    /**
     * Notify post author about new comment.
     */
    public function notifyNewComment(\App\Models\Comment $comment): void
    {
        $post = $comment->post;
        $postAuthor = $post->user;

        // Don't notify if user commented on their own post
        if ($postAuthor->id !== $comment->user_id) {
            $postAuthor->notify(new \App\Notifications\NewCommentNotification($comment));
        }
    }

    /**
     * Notify post/comment author about new vote (optional).
     */
    public function notifyNewVote($vote): void
    {
        // This is optional - can be implemented if needed
        // For now, we'll skip to avoid notification spam
    }

    /**
     * Notify user about new follow.
     */
    public function notifyNewFollow(\App\Models\User $user, \App\Models\User $follower): void
    {
        $user->notify(new \App\Notifications\NewFollowNotification($follower));
    }

    /**
     * Notify admin about content report.
     */
    public function notifyContentReported(\App\Models\ContentReport $report): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ContentReportedNotification($report));
        }
    }

    /**
     * Notify reporter about report resolution.
     */
    public function notifyReportResolved(\App\Models\ContentReport $report): void
    {
        $reporter = $report->user;
        $reporter->notify(new \App\Notifications\ReportResolvedNotification($report));
    }

    /**
     * Notify user about mention (future feature).
     */
    public function notifyMention(\App\Models\User $user, $mentionable): void
    {
        // Future implementation for mentions
    }
}

