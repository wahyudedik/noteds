<?php

namespace App\Console\Commands;

use App\Jobs\PublishScheduledPost;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts that are ready to be published';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $posts = Post::where('publish_status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts ready to publish.');
            return Command::SUCCESS;
        }

        $this->info("Found {$posts->count()} scheduled post(s) ready to publish.");

        foreach ($posts as $post) {
            PublishScheduledPost::dispatch($post);
            $this->line("Dispatched job to publish post: {$post->title} (ID: {$post->id})");
        }

        $this->info('All scheduled posts have been queued for publishing.');
        return Command::SUCCESS;
    }
}
