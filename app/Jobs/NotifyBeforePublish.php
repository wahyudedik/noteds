<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\NotificationService;
use App\Models\RecurrenceRule;
use App\Services\RecurrenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyBeforePublish implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $minutesAhead = 30) {}

    public function handle(NotificationService $notificationService): void
    {
        $from = now()->addMinutes($this->minutesAhead)->startOfMinute();
        $to = now()->addMinutes($this->minutesAhead + 1)->startOfMinute();
        $posts = Post::where('publish_status', 'scheduled')
            ->whereBetween('scheduled_at', [$from, $to])
            ->get();
        foreach ($posts as $post) {
            if (!$post->relationLoaded('user')) {
                $post->load('user');
            }
            $notificationService->notifyScheduledPostReminder($post);
            \DB::table('scheduling_audits')->insert([
                'scheduleable_type' => Post::class,
                'scheduleable_id' => $post->id,
                'user_id' => $post->user_id,
                'action' => 'notify',
                'meta' => json_encode(['minutes_ahead' => $this->minutesAhead]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $recurrenceService = app(RecurrenceService::class);
        $rules = RecurrenceRule::where('scheduleable_type', Post::class)->get();
        foreach ($rules as $rule) {
            $occurrences = $recurrenceService->windowUpcoming($rule, $this->minutesAhead, 1);
            if (!empty($occurrences)) {
                $post = Post::find($rule->scheduleable_id);
                if ($post) {
                    if (!$post->relationLoaded('user')) {
                        $post->load('user');
                    }
                    $notificationService->notifyScheduledPostReminder($post);
                    \DB::table('scheduling_audits')->insert([
                        'scheduleable_type' => Post::class,
                        'scheduleable_id' => $post->id,
                        'user_id' => $post->user_id,
                        'action' => 'notify',
                        'meta' => json_encode(['recurrence' => true, 'minutes_ahead' => $this->minutesAhead]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
