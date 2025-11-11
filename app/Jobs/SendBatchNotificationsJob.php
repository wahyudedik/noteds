<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBatchNotificationsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $userIds,
        public string $type,
        public string $title,
        public string $message,
        public ?string $link = null,
        public ?array $data = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        try {
            $users = User::whereIn('id', $this->userIds)->get();
            
            $sent = 0;
            foreach ($users as $user) {
                if ($this->batch()->cancelled()) {
                    break;
                }

                try {
                    $notificationService->create(
                        $user,
                        $this->type,
                        $this->title,
                        $this->message,
                        $this->link,
                        $this->data
                    );
                    $sent++;
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification to user', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Batch notifications sent', [
                'total' => count($this->userIds),
                'sent' => $sent,
                'type' => $this->type,
            ]);
        } catch (\Exception $e) {
            Log::error('Batch notification job failed', [
                'user_ids' => $this->userIds,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

