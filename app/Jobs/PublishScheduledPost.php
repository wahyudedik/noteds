<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Check if post is still scheduled and ready to publish
        if ($this->post->isScheduled() && $this->post->canPublish()) {
            $this->post->update([
                'publish_status' => 'published',
                'status' => 'active',
            ]);

            // Notify the author
            $notificationService->notifyPostPublished($this->post);
        }
    }
}
