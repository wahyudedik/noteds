<?php

namespace App\Console\Commands;

use App\Services\EscrowService;
use Illuminate\Console\Command;

class AutoReleaseEscrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:auto-release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-release escrows that have passed their auto-release date';

    /**
     * Execute the console command.
     */
    public function handle(EscrowService $escrowService): int
    {
        $this->info('Starting auto-release of escrows...');

        $released = $escrowService->autoReleaseEscrows();

        $this->info("Successfully auto-released {$released} escrow(s).");

        return Command::SUCCESS;
    }
}

