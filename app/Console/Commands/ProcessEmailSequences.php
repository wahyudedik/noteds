<?php

namespace App\Console\Commands;

use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

class ProcessEmailSequences extends Command
{
    protected $signature = 'email:process-sequences';
    protected $description = 'Process and send email sequences';

    public function handle(EmailCampaignService $emailCampaignService): int
    {
        $this->info('Processing email sequences...');
        
        $processed = $emailCampaignService->processEmailSequences();
        
        $this->info("Processed {$processed} sequence emails.");
        
        return Command::SUCCESS;
    }
}

