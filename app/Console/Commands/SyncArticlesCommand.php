<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class SyncArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:sync {--source=all : Source to sync (rss, reddit, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync articles from RSS feeds and Reddit';

    /**
     * Execute the console command.
     */
    public function handle(ArticleService $articleService): int
    {
        $source = $this->option('source');
        
        if (!in_array($source, ['all', 'rss', 'reddit'])) {
            $this->error('Invalid source. Use: all, rss, or reddit');
            return Command::FAILURE;
        }

        $this->info("Syncing articles from: {$source}");

        try {
            $count = $articleService->syncArticles($source);
            
            $this->info("Successfully synced {$count} articles.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to sync articles: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
