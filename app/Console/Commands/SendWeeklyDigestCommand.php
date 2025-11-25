<?php

namespace App\Console\Commands;

use App\Jobs\SendWeeklyDigestJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendWeeklyDigestCommand extends Command
{
    protected $signature = 'notifications:send-weekly-digest';
    protected $description = 'Send weekly email digest to users who have enabled it';

    public function handle(): int
    {
        $this->info('Sending weekly email digests...');

        // Get users who want weekly digest and haven't received one this week
        $users = User::where('email_digest_frequency', 'weekly')
            ->whereNotNull('email')
            ->where(function ($query) {
                $query->whereNull('last_digest_sent_at')
                    ->orWhere('last_digest_sent_at', '<', now()->subWeek());
            })
            ->get();

        $count = 0;
        foreach ($users as $user) {
            // Check if it's the right time for this user (Monday is default)
            if ($user->email_digest_time) {
                $timezone = $user->email_digest_timezone ?? config('app.timezone', 'UTC');
                $userTime = now()->setTimezone($timezone);
                
                // Only send on Monday (or if no day preference, send on Monday)
                if ($userTime->dayOfWeek !== 1) {
                    continue;
                }

                $digestTime = $userTime->copy()->setTimeFromTimeString($user->email_digest_time);

                // Only send if current time matches digest time (within 1 hour window)
                if ($userTime->diffInHours($digestTime) > 1) {
                    continue;
                }
            } else {
                // Default: only send on Monday
                $timezone = $user->email_digest_timezone ?? config('app.timezone', 'UTC');
                $userTime = now()->setTimezone($timezone);
                if ($userTime->dayOfWeek !== 1) {
                    continue;
                }
            }

            SendWeeklyDigestJob::dispatch($user->id)->onQueue('emails');
            $count++;
        }

        $this->info("Queued {$count} weekly digest emails.");

        return Command::SUCCESS;
    }
}
