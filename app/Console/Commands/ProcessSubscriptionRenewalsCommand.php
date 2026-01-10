<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due subscription renewals';

    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing subscription renewals...');

        try {
            $this->subscriptionService->processDueRenewals();
            $this->info('Subscription renewals processed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process renewals: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
