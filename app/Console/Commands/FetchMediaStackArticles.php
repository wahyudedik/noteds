<?php

namespace App\Console\Commands;

use App\Services\MediaStackService;
use Illuminate\Console\Command;

class FetchMediaStackArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediastack:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from MediaStack API and store to database';

    /**
     * Execute the console command.
     */
    public function handle(MediaStackService $mediaStackService): int
    {
        $this->info('Fetching articles from MediaStack API...');

        // Check API usage
        $currentUsage = $mediaStackService->getCurrentMonthUsage();
        $limit = $mediaStackService->getApiLimit();
        $this->info("Current API usage: {$currentUsage}/{$limit} requests this month");

        if ($mediaStackService->isUsageLimitReached()) {
            $this->warn('API usage limit reached. Skipping fetch.');
            return Command::SUCCESS;
        }

        try {
            // Fetch for both English and Indonesian as requested for Explorer feature
            $articles = $mediaStackService->fetchAndStoreArticles([
                'categories' => config('mediastack.default_categories'),
                'language' => 'en,id', // Support both languages
                'limit' => config('mediastack.default_limit', 100),
            ]);

            $count = count($articles);
            $newUsage = $mediaStackService->getCurrentMonthUsage();
            $this->info("Successfully fetched and stored {$count} articles.");
            $this->info("API usage after fetch: {$newUsage}/{$limit} requests this month");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to fetch articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
