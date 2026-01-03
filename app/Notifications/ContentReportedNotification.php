<?php

namespace App\Notifications;

use App\Models\ContentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ContentReport $report
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reportableType = class_basename($this->report->reportable_type);
        
        return (new MailMessage)
            ->subject('New Content Report')
            ->line("A new {$reportableType} has been reported.")
            ->line("Reason: {$this->report->reason}")
            ->line("Reported by: {$this->report->user->name}")
            ->action('Review Report', route('admin.reports.show', $this->report));
    }

    public function toArray(object $notifiable): array
    {
        $reportableType = class_basename($this->report->reportable_type);
        
        return [
            'type' => 'content_reported',
            'report_id' => $this->report->id,
            'reportable_type' => $reportableType,
            'reportable_id' => $this->report->reportable_id,
            'reason' => $this->report->reason,
            'reporter_name' => $this->report->user->name,
            'title' => "New {$reportableType} Report",
            'message' => "A {$reportableType} has been reported: {$this->report->reason}",
        ];
    }
}

