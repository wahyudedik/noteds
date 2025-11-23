<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserEmailPreference;
use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

class SendWeeklyDigest extends Command
{
    protected $signature = 'email:weekly-digest';
    protected $description = 'Send weekly digest emails to users';

    public function handle(EmailCampaignService $emailCampaignService): int
    {
        $this->info('Sending weekly digest emails...');
        
        // Get users who have weekly digest enabled (default is true)
        $users = User::where(function ($query) {
            $query->whereHas('emailPreference', function ($q) {
                $q->where('weekly_digest', true);
            })->orWhereDoesntHave('emailPreference');
        })->get();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($users as $user) {
            try {
                if ($emailCampaignService->sendWeeklyDigest($user)) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to send to {$user->email}: {$e->getMessage()}");
                $failed++;
            }
        }
        
        $this->info("Sent {$sent} weekly digest emails. Failed: {$failed}");
        
        return Command::SUCCESS;
    }
}

