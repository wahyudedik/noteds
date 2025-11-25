<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyDigestJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendDailyDigestCommand extends Command
{
    protected $signature = 'notifications:send-daily-digest';
    protected $description = 'Send daily email digest to users who have enabled it';

    public function handle(): int
    {
        $this->info('Sending daily email digests...');

        // Get users who want daily digest and haven't received one today
        $users = User::where('email_digest_frequency', 'daily')
            ->whereNotNull('email')
            ->where(function ($query) {
                $query->whereNull('last_digest_sent_at')
                    ->orWhereDate('last_digest_sent_at', '<', now()->toDateString());
            })
            ->get();

        $count = 0;
        foreach ($users as $user) {
            // Check if it's the right time for this user
            if ($user->email_digest_time) {
                $timezone = $user->email_digest_timezone ?? config('app.timezone', 'UTC');
                $userTime = now()->setTimezone($timezone);
                $digestTime = $userTime->copy()->setTimeFromTimeString($user->email_digest_time);

                // Only send if current time matches digest time (within 1 hour window)
                if ($userTime->diffInHours($digestTime) > 1) {
                    continue;
                }
            }

            SendDailyDigestJob::dispatch($user->id)->onQueue('emails');
            $count++;
        }

        $this->info("Queued {$count} daily digest emails.");

        return Command::SUCCESS;
    }
}
