<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ContentRecommendationEngine;

class RefreshRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'recommendations:refresh {--user-id= : Specific user ID to refresh}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh content recommendations cache';

    /**
     * Execute the console command.
     */
    public function handle(ContentRecommendationEngine $engine): int
    {
        $this->info('Starting recommendations refresh...');

        try {
            $userId = $this->option('user-id');

            if ($userId) {
                $user = \App\Models\User::find($userId);
                if (!$user) {
                    $this->error("User with ID {$userId} not found");
                    return Command::FAILURE;
                }

                $this->info("Refreshing recommendations for user: {$user->name}");
                $engine->refreshUserRecommendations($user);
                $this->info('✅ User recommendations refreshed successfully!');
            } else {
                $this->info('Refreshing all recommendations cache...');
                $engine->refreshAllRecommendations();
                $this->info('✅ All recommendations cache refreshed successfully!');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error refreshing recommendations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
