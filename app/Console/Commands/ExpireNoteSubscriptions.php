<?php

namespace App\Console\Commands;

use App\Services\NoteSubscriptionService;
use Illuminate\Console\Command;

class ExpireNoteSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire note subscriptions that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(NoteSubscriptionService $subscriptionService): int
    {
        $this->info('Starting expiration check for note subscriptions...');

        $expired = $subscriptionService->expireSubscriptions();

        $this->info("Successfully expired {$expired} subscription(s).");

        return Command::SUCCESS;
    }
}

