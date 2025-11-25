<?php

namespace App\Jobs;

use App\Mail\DailyDigestMail;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestJob implements ShouldQueue
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
        
        if (!$user || !$user->wantsDailyDigest() || empty($user->email)) {
            return;
        }

        // Get unread notifications from last 24 hours
        $notifications = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notifications->isEmpty()) {
            return; // No notifications to send
        }

        // Group notifications by type for summary
        $summary = [
            'total' => $notifications->count(),
            'by_type' => $notifications->groupBy('type')->map->count()->toArray(),
        ];

        // Send email
        Mail::to($user->email)->send(new DailyDigestMail($user, $notifications->toArray(), $summary));

        // Update last digest sent timestamp
        $user->update(['last_digest_sent_at' => now()]);
    }
}
