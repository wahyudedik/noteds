<?php

namespace App\Console\Commands;

use App\Services\ShareToEarnService;
use Illuminate\Console\Command;

class CalculateMonthlyShareRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'share:calculate-monthly-rewards {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and distribute monthly share rewards';

    /**
     * Execute the console command.
     */
    public function handle(ShareToEarnService $shareToEarnService): int
    {
        $month = $this->argument('month') ?? now()->subMonth()->format('Y-m');

        $this->info("Calculating monthly share rewards for {$month}...");

        try {
            $shareToEarnService->calculateMonthlyRewards($month);
            $this->info("Monthly share rewards calculated successfully for {$month}!");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to calculate monthly share rewards: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
