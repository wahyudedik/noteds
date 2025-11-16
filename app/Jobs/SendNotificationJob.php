<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $message,
        public ?string $link = null,
        public ?array $data = null,
    ) {}

    /**
     * Execute the job.
     * Note: Notification is already created in NotificationService->create().
     * This job only handles email sending for forum notifications.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $user = User::find($this->userId);
            
            if (!$user) {
                Log::warning('Notification job skipped: User not found', [
                    'user_id' => $this->userId,
                    'type' => $this->type,
                ]);
                return;
            }

            // Send email if needed (forum notifications)
            // The notification itself is already created in NotificationService->create()
            if (str_starts_with($this->type, 'forum_')) {
                if ($user->wantsForumEmail($this->type) && !empty($user->email)) {
                    \App\Jobs\SendEmailJob::dispatch(
                        $user->email,
                        new \App\Mail\ForumNotificationMail($this->title, $this->message, $this->link)
                    )->onQueue('emails');
                }
            }

            Log::debug('Notification processed via queue', [
                'user_id' => $this->userId,
                'type' => $this->type,
            ]);
        } catch (\Exception $e) {
            Log::error('Notification job failed', [
                'user_id' => $this->userId,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed permanently', [
            'user_id' => $this->userId,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}

