<?php

namespace App\Console\Commands;

use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

class SendAbandonedCartEmails extends Command
{
    protected $signature = 'email:abandoned-cart';
    protected $description = 'Send abandoned cart reminder emails';

    public function handle(EmailCampaignService $emailCampaignService): int
    {
        $this->info('Sending abandoned cart emails...');
        
        $sent = $emailCampaignService->sendAbandonedCartEmails();
        
        $this->info("Sent {$sent} abandoned cart emails.");
        
        return Command::SUCCESS;
    }
}

