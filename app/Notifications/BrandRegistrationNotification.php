<?php

namespace App\Notifications;

use App\Models\BrandRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrandRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BrandRegistration $registration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Brand Registration Request')
            ->line("A new brand registration has been submitted by {$this->registration->user->name}.")
            ->line("Company: {$this->registration->company_name}")
            ->line("Business Type: {$this->registration->business_type}")
            ->action('Review Registration', route('admin.brand-approvals.show', $this->registration->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'brand_registration',
            'registration_id' => $this->registration->id,
            'company_name' => $this->registration->company_name,
            'user_name' => $this->registration->user->name,
            'user_email' => $this->registration->user->email,
            'business_type' => $this->registration->business_type,
            'created_at' => $this->registration->created_at,
            'title' => 'New Brand Registration Request',
            'message' => "{$this->registration->user->name} has submitted a brand registration for {$this->registration->company_name}.",
        ];
    }
}

