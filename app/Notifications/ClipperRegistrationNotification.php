<?php

namespace App\Notifications;

use App\Models\ClipperRegistration;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClipperRegistrationNotification extends Notification
{
    public function __construct(
        public ClipperRegistration $registration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Clipper Registration Request')
            ->line("A new clipper registration has been submitted by {$this->registration->user->name}.")
            ->line("Email: {$this->registration->user->email}")
            ->action('Review Registration', route('admin.clipper-approvals.show', $this->registration->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'clipper_registration',
            'registration_id' => $this->registration->id,
            'user_id' => $this->registration->user_id,
            'user_name' => $this->registration->user->name,
            'user_email' => $this->registration->user->email,
            'created_at' => $this->registration->created_at,
            'title' => 'New Clipper Registration Request',
            'message' => "{$this->registration->user->name} has submitted a clipper registration request.",
        ];
    }
}

