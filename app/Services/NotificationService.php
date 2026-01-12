<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Withdrawal;
use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use App\Models\Post;
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
     * Notify buyer about order status update.
     */
    public function notifyOrderStatusUpdate(Order $order): void
    {
        if ($order->buyer) {
            // Create database notification
            $order->buyer->notify(new class($order) extends \Illuminate\Notifications\Notification {
                use \Illuminate\Bus\Queueable;
                
                public function __construct(public Order $order) {}
                
                public function via($notifiable): array
                {
                    return ['database'];
                }
                
                public function toArray($notifiable): array
                {
                    return [
                        'type' => 'order_status_update',
                        'order_id' => $this->order->id,
                        'title' => 'Order Status Updated',
                        'message' => "Your order #{$this->order->order_number} status has been updated to {$this->order->status}",
                        'order_number' => $this->order->order_number,
                        'status' => $this->order->status,
                    ];
                }
            });

            // Send email notification
            try {
                \Illuminate\Support\Facades\Mail::to($order->buyer->email)
                    ->send(new \App\Mail\OrderStatusUpdateMail($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send order status update email: ' . $e->getMessage());
            }
        }
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
     * Notify clipper about view validation.
     */
    public function notifyViewValidated(\App\Models\Clip $clip): void
    {
        $clip->clipper->notify(new \App\Notifications\ViewValidatedNotification($clip));
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

    /**
     * Notify user about successful top up.
     */
    public function notifyTopUpSuccess(\App\Models\TopUp $topUp): void
    {
        $user = $topUp->user;
        
        // Create database notification
        $user->notify(new class($topUp) extends \Illuminate\Notifications\Notification {
            use \Illuminate\Bus\Queueable;
            
            public function __construct(public \App\Models\TopUp $topUp) {}
            
            public function via($notifiable): array
            {
                return ['database'];
            }
            
            public function toArray($notifiable): array
            {
                return [
                    'type' => 'topup_success',
                    'top_up_id' => $this->topUp->id,
                    'title' => 'Top Up Successful',
                    'message' => 'Your wallet has been topped up with Rp ' . number_format($this->topUp->amount, 0, ',', '.'),
                    'amount' => $this->topUp->amount,
                ];
            }
        });

        // Optional: Send email notification
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new class($topUp) extends \Illuminate\Mail\Mailable {
                    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels;

                    public function __construct(public \App\Models\TopUp $topUp) {}

                    public function build()
                    {
                        return $this->subject('Top Up Successful - ' . config('app.name'))
                            ->view('emails.topup-success')
                            ->with([
                                'topUp' => $this->topUp,
                                'amount' => number_format($this->topUp->amount, 0, ',', '.'),
                            ]);
                    }
                });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send top up success email: ' . $e->getMessage());
        }
    }

    /**
     * Notify user about refund/adjustment.
     */
    public function notifyRefundProcessed(\App\Models\Refund $refund): void
    {
        $user = $refund->user;
        
        // Create database notification
        $user->notify(new class($refund) extends \Illuminate\Notifications\Notification {
            use \Illuminate\Bus\Queueable;
            
            public function __construct(public \App\Models\Refund $refund) {}
            
            public function via($notifiable): array
            {
                return ['database'];
            }
            
            public function toArray($notifiable): array
            {
                $walletType = $this->refund->wallet_type === 'creator' ? 'Creator Wallet' : 'Marketplace Wallet';
                $typeLabel = $this->refund->type === 'refund' ? 'Refund' : 'Adjustment';
                $amount = number_format($this->refund->amount, 0, ',', '.');
                $action = $this->refund->type === 'refund' ? 'credited' : 'deducted';
                
                return [
                    'type' => 'refund_processed',
                    'refund_id' => $this->refund->id,
                    'title' => ucfirst($this->refund->type) . ' Processed',
                    'message' => "Your {$walletType} has been {$action} with Rp {$amount} ({$typeLabel})",
                    'amount' => $this->refund->amount,
                    'wallet_type' => $this->refund->wallet_type,
                    'refund_type' => $this->refund->type,
                ];
            }
        });
    }

    /**
     * Notify admin about new campaign created.
     */
    public function notifyCampaignCreated(\App\Models\Campaign $campaign): void
    {
        // Ensure relationships are loaded
        if (!$campaign->relationLoaded('creator')) {
            $campaign->load('creator');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\CampaignCreatedNotification($campaign));
        }
    }

    /**
     * Notify admin about new clip submitted.
     */
    public function notifyClipSubmitted(\App\Models\Clip $clip): void
    {
        // Ensure relationships are loaded
        if (!$clip->relationLoaded('clipper')) {
            $clip->load('clipper');
        }
        if (!$clip->relationLoaded('campaign')) {
            $clip->load('campaign');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ClipSubmittedNotification($clip));
        }
    }

    /**
     * Notify admin about new product created.
     */
    public function notifyProductCreated(\App\Models\Product $product): void
    {
        // Ensure relationships are loaded
        if (!$product->relationLoaded('seller')) {
            $product->load('seller');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ProductCreatedNotification($product));
        }
    }

    /**
     * Notify admin about fraud detected in clip.
     */
    public function notifyFraudDetected(\App\Models\Clip $clip, string $reason, ?float $stabilityScore = null): void
    {
        // Ensure relationships are loaded
        if (!$clip->relationLoaded('clipper')) {
            $clip->load('clipper');
        }
        if (!$clip->relationLoaded('campaign')) {
            $clip->load('campaign');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\FraudDetectedNotification($clip, $reason, $stabilityScore));
        }
    }

    /**
     * Notify admin about new order created (optional, for monitoring).
     */
    public function notifyOrderCreated(Order $order): void
    {
        // Ensure relationships are loaded
        if (!$order->relationLoaded('buyer')) {
            $order->load('buyer');
        }
        if (!$order->relationLoaded('product')) {
            $order->load('product');
        }
        if ($order->product && !$order->product->relationLoaded('seller')) {
            $order->product->load('seller');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\OrderCreatedNotification($order));
        }
    }

    /**
     * Notify brand about campaign suspended.
     */
    public function notifyCampaignSuspended(\App\Models\Campaign $campaign, string $reason): void
    {
        // Ensure relationships are loaded
        if (!$campaign->relationLoaded('creator')) {
            $campaign->load('creator');
        }
        
        if ($campaign->creator) {
            $campaign->creator->notify(new \App\Notifications\CampaignSuspendedNotification($campaign, $reason));
        }
    }

    /**
     * Notify seller about product approved.
     */
    public function notifyProductApproved(\App\Models\Product $product): void
    {
        // Ensure relationships are loaded
        if (!$product->relationLoaded('seller')) {
            $product->load('seller');
        }
        
        if ($product->seller) {
            $product->seller->notify(new \App\Notifications\ProductApprovedNotification($product));
        }
    }

    /**
     * Notify seller about product rejected.
     */
    public function notifyProductRejected(\App\Models\Product $product, string $reason): void
    {
        // Ensure relationships are loaded
        if (!$product->relationLoaded('seller')) {
            $product->load('seller');
        }
        
        if ($product->seller) {
            $product->seller->notify(new \App\Notifications\ProductRejectedNotification($product, $reason));
        }
    }

    /**
     * Notify buyer and seller about order cancelled.
     */
    public function notifyOrderCancelled(Order $order, string $reason): void
    {
        // Notify buyer
        if ($order->buyer) {
            $order->buyer->notify(new \App\Notifications\OrderCancelledNotification($order, $reason));
        }
        
        // Notify seller
        if ($order->product && $order->product->seller) {
            $order->product->seller->notify(new \App\Notifications\OrderCancelledNotification($order, $reason));
        }
    }

    /**
     * Notify buyer about payment failed.
     */
    public function notifyPaymentFailed(Order $order, string $failureReason): void
    {
        if ($order->buyer) {
            $order->buyer->notify(new \App\Notifications\PaymentFailedNotification($order, $failureReason));
        }
    }

    /**
     * Notify admin about new support ticket.
     */
    public function notifyNewSupportTicket(SupportTicket $ticket): void
    {
        // Ensure user relationship is loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }

        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewSupportTicketNotification($ticket));
        }
    }

    /**
     * Notify user or admin about new support ticket response.
     */
    public function notifySupportTicketResponse(SupportTicket $ticket, SupportTicketResponse $response): void
    {
        // Ensure relationships are loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }
        if (!$response->relationLoaded('user')) {
            $response->load('user');
        }

        $isAdminResponse = $response->is_admin_response;
        
        if ($isAdminResponse) {
            // Admin responded, notify the ticket owner (user)
            if ($ticket->user) {
                $ticket->user->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
            }
        } else {
            // User responded, notify assigned admin or all admins if not assigned
            if ($ticket->assigned_to) {
                $assignedAdmin = User::find($ticket->assigned_to);
                if ($assignedAdmin) {
                    $assignedAdmin->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
                }
            } else {
                // Notify all admins if no admin is assigned
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\SupportTicketResponseNotification($ticket, $response));
                }
            }
        }
    }

    /**
     * Notify admin about new brand registration.
     */
    public function notifyBrandRegistration(\App\Models\BrandRegistration $registration): void
    {
        // Ensure relationships are loaded
        if (!$registration->relationLoaded('user')) {
            $registration->load('user');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\BrandRegistrationNotification($registration));
        }
    }

    /**
     * Notify admin about new clipper registration.
     */
    public function notifyClipperRegistration(\App\Models\ClipperRegistration $registration): void
    {
        // Ensure relationships are loaded
        if (!$registration->relationLoaded('user')) {
            $registration->load('user');
        }
        
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ClipperRegistrationNotification($registration));
        }
    }

    /**
     * Notify user and admin about support ticket status update.
     */
    public function notifySupportTicketStatusUpdate(SupportTicket $ticket, string $oldStatus, string $newStatus): void
    {
        // Ensure user relationship is loaded
        if (!$ticket->relationLoaded('user')) {
            $ticket->load('user');
        }

        // Notify the ticket owner (user)
        if ($ticket->user) {
            $ticket->user->notify(new \App\Notifications\SupportTicketStatusUpdateNotification($ticket, $oldStatus, $newStatus));
        }

        // Notify assigned admin if exists
        if ($ticket->assigned_to) {
            $assignedAdmin = User::find($ticket->assigned_to);
            if ($assignedAdmin) {
                $assignedAdmin->notify(new \App\Notifications\SupportTicketStatusUpdateNotification($ticket, $oldStatus, $newStatus));
            }
        }
    }

    /**
     * Notify post author when their post is reposted.
     */
    public function notifyPostReposted(\App\Models\Post $post, User $reposter, ?\App\Models\Repost $repost = null): void
    {
        // Ensure relationships are loaded
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user && $post->user_id !== $reposter->id) {
            $post->user->notify(new \App\Notifications\PostRepostedNotification($post, $reposter, $repost));
        }
    }

    /**
     * Notify post author when their scheduled post is published.
     */
    public function notifyPostPublished(Post $post): void
    {
        // Ensure relationships are loaded
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user) {
            $post->user->notify(new \App\Notifications\PostPublishedNotification($post));
        }
    }

    /**
     * Notify user about collaboration invitation.
     */
    public function notifyCollaborationInvitation(PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);
        
        if ($collaboration->user) {
            $collaboration->user->notify(new \App\Notifications\CollaborationInvitationNotification($collaboration));
        }
    }

    /**
     * Notify post owner when collaboration is accepted.
     */
    public function notifyCollaborationAccepted(PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);
        
        if ($collaboration->post->user) {
            $collaboration->post->user->notify(new \App\Notifications\CollaborationAcceptedNotification($collaboration));
        }
    }

    /**
     * Notify post owner when collaboration is rejected.
     */
    public function notifyCollaborationRejected(PostCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'post.user']);
        
        if ($collaboration->post->user) {
            $collaboration->post->user->notify(new \App\Notifications\CollaborationRejectedNotification($collaboration));
        }
    }

    /**
     * Notify post owner when post is edited by collaborator.
     */
    public function notifyPostEditedByCollaborator(Post $post, User $collaborator): void
    {
        if (!$post->relationLoaded('user')) {
            $post->load('user');
        }

        if ($post->user && $post->user_id !== $collaborator->id) {
            $post->user->notify(new \App\Notifications\PostEditedByCollaboratorNotification($post, $collaborator));
        }
    }

    /**
     * Notify user about campaign collaboration invitation.
     */
    public function notifyCampaignCollaborationInvitation(\App\Models\CampaignCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'campaign.creator']);
        
        if ($collaboration->user) {
            $collaboration->user->notify(new \App\Notifications\CampaignCollaborationInvitationNotification($collaboration));
        }
    }

    /**
     * Notify campaign owner when collaboration is accepted.
     */
    public function notifyCampaignCollaborationAccepted(\App\Models\CampaignCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'campaign.creator']);
        
        if ($collaboration->campaign->creator) {
            $collaboration->campaign->creator->notify(new \App\Notifications\CampaignCollaborationAcceptedNotification($collaboration));
        }
    }

    /**
     * Notify campaign owner when collaboration is rejected.
     */
    public function notifyCampaignCollaborationRejected(\App\Models\CampaignCollaborator $collaboration): void
    {
        $collaboration->load(['user', 'campaign.creator']);
        
        if ($collaboration->campaign->creator) {
            $collaboration->campaign->creator->notify(new \App\Notifications\CampaignCollaborationRejectedNotification($collaboration));
        }
    }
}

