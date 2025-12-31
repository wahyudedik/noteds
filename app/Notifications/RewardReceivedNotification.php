<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RewardReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Clip $clip
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reward Received')
            ->line("You have received a reward for your clip!")
            ->line("Campaign: {$this->clip->campaign->title}")
            ->line("Reward: Rp " . number_format($this->clip->approved_reward, 0, ',', '.'))
            ->action('View Wallet', route('clipper.wallet.clipper'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reward_received',
            'clip_id' => $this->clip->id,
            'title' => 'Reward Received',
            'message' => "You have received a reward for your clip! Campaign: {$this->clip->campaign->title}",
            'campaign_title' => $this->clip->campaign->title,
            'reward' => $this->clip->approved_reward,
        ];
    }
}
