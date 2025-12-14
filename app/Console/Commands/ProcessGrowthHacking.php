<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GrowthHackingService;
use App\Models\User;

class ProcessGrowthHacking extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'growth:process {--type=all : Type of growth task (streaks, nudges, bonuses, all)}';

    /**
     * The console command description.
     */
    protected $description = 'Process growth hacking tasks (streaks, engagement nudges, bonuses)';

    /**
     * Execute the console command.
     */
    public function handle(GrowthHackingService $service): int
    {
        $type = $this->option('type');

        $this->info('Starting growth hacking processing...');
        $this->newLine();

        try {
            if ($type === 'streaks' || $type === 'all') {
                $this->processStreaks($service);
            }

            if ($type === 'nudges' || $type === 'all') {
                $this->processEngagementNudges($service);
            }

            if ($type === 'bonuses' || $type === 'all') {
                $this->processQualityBonuses($service);
            }

            $this->newLine();
            $this->info('✅ Growth hacking processing completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error processing growth hacking: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Process user streaks
     */
    private function processStreaks(GrowthHackingService $service): void
    {
        $this->info('📊 Processing user streaks...');

        $activeUsers = User::whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(2))
            ->get();

        $bar = $this->output->createProgressBar($activeUsers->count());
        $bar->start();

        $processed = 0;
        foreach ($activeUsers as $user) {
            try {
                $service->processStreakRewards($user);
                $processed++;
            } catch (\Exception $e) {
                // Continue processing other users
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   Processed {$processed} users for streak rewards");
    }

    /**
     * Process engagement nudges
     */
    private function processEngagementNudges(GrowthHackingService $service): void
    {
        $this->info('📧 Processing engagement nudges...');

        try {
            $service->sendEngagementNudges();
            $this->info('   Engagement emails queued successfully');
        } catch (\Exception $e) {
            $this->error('   Failed to queue engagement emails: ' . $e->getMessage());
        }
    }

    /**
     * Process quality bonuses
     */
    private function processQualityBonuses(GrowthHackingService $service): void
    {
        $this->info('💰 Processing creator quality bonuses...');

        try {
            $service->processQualityBonuses();
            $this->info('   Quality bonuses processed successfully');
        } catch (\Exception $e) {
            $this->error('   Failed to process quality bonuses: ' . $e->getMessage());
        }
    }
}
