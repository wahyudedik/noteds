<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:publish-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish forum posts that have reached their scheduled time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $scheduledPosts = Post::where('is_published', false)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($scheduledPosts->isEmpty()) {
            $this->info('No scheduled posts to publish.');
            return 0;
        }

        foreach ($scheduledPosts as $post) {
            $post->update([
                'is_published' => true,
                'scheduled_at' => null,
                'published_at' => $now,
            ]);
        }

        $this->info(sprintf('Published %d scheduled post(s).', $scheduledPosts->count()));

        return 0;
    }
}
