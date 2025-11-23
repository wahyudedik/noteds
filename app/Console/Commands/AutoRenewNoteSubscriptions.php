<?php

namespace App\Console\Commands;

use App\Services\NoteSubscriptionService;
use Illuminate\Console\Command;

class AutoRenewNoteSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:auto-renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-renew note subscriptions that are due for renewal';

    /**
     * Execute the console command.
     */
    public function handle(NoteSubscriptionService $subscriptionService): int
    {
        $this->info('Starting auto-renewal of note subscriptions...');

        $renewed = $subscriptionService->autoRenewSubscriptions();

        $this->info("Successfully renewed {$renewed} subscription(s).");

        return Command::SUCCESS;
    }
}

