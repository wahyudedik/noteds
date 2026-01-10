<?php

namespace App\Console\Commands;

use App\Services\TrendingService;
use Illuminate\Console\Command;

class CalculateTrendingPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:calculate-trending {--limit= : Limit number of posts to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate trending scores for posts';

    /**
     * Execute the console command.
     */
    public function handle(TrendingService $trendingService)
    {
        $this->info('Calculating trending scores...');
        
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        
        $processed = $trendingService->calculateTrendingScores($limit);
        
        $this->info("Successfully calculated trending scores for {$processed} posts.");
        
        return Command::SUCCESS;
    }
}
