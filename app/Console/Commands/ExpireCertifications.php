<?php

namespace App\Console\Commands;

use App\Services\CertificationService;
use Illuminate\Console\Command;

class ExpireCertifications extends Command
{
    protected $signature = 'certifications:expire';
    protected $description = 'Check and expire certifications that have passed their expiration date';

    public function handle(CertificationService $certificationService): int
    {
        $this->info('Checking for expired certifications...');
        
        $expired = $certificationService->expireCertifications();
        
        $this->info("Expired {$expired} certifications.");
        
        return Command::SUCCESS;
    }
}

