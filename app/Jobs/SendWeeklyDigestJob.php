<?php

namespace App\Jobs;

use App\Mail\WeeklyDigestMail;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public string $userId
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        
        if (!$user || !$user->wantsWeeklyDigest() || empty($user->email)) {
            return;
        }

        // Get unread notifications from last 7 days
        $notifications = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->where('created_at', '>=', now()->subWeek())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notifications->isEmpty()) {
            return; // No notifications to send
        }

        // Get recommended notes (can be enhanced with recommendation engine)
        $recommendedNotes = collect(); // Placeholder for now

        // Group notifications by type for summary
        $summary = [
            'total' => $notifications->count(),
            'by_type' => $notifications->groupBy('type')->map->count()->toArray(),
        ];

        // Send email
        Mail::to($user->email)->send(new WeeklyDigestMail($user, $recommendedNotes, $notifications->toArray(), $summary));

        // Update last digest sent timestamp
        $user->update(['last_digest_sent_at' => now()]);
    }
}
