<?php

namespace App\Notifications;

use App\Models\Clip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FraudDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Clip $clip,
        public string $reason,
        public ?float $stabilityScore = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $clipperName = $this->clip->clipper?->name ?? 'Unknown';
        $campaignTitle = $this->clip->campaign?->title ?? 'Unknown Campaign';
        
        return (new MailMessage)
            ->subject('⚠️ Fraud Detected in Clip')
            ->line("⚠️ Fraud has been detected in a clip submitted by {$clipperName}.")
            ->line("Campaign: {$campaignTitle}")
            ->line("Reason: {$this->reason}")
            ->when($this->stabilityScore !== null, function ($mail) {
                return $mail->line("Stability Score: " . number_format($this->stabilityScore, 2));
            })
            ->action('Review Clip', route('admin.clips.show', $this->clip));
    }

    public function toArray(object $notifiable): array
    {
        $clipperName = $this->clip->clipper?->name ?? 'Unknown';
        $campaignTitle = $this->clip->campaign?->title ?? 'Unknown Campaign';
        
        return [
            'type' => 'fraud_detected',
            'clip_id' => $this->clip->id,
            'campaign_id' => $this->clip->campaign_id,
            'campaign_title' => $campaignTitle,
            'clipper_name' => $clipperName,
            'fraud_reason' => $this->reason,
            'stability_score' => $this->stabilityScore,
            'title' => '⚠️ Fraud Detected',
            'message' => "Fraud detected in clip from {$clipperName} for campaign '{$campaignTitle}'. Reason: {$this->reason}",
        ];
    }
}

