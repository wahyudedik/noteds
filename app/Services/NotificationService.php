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
}

