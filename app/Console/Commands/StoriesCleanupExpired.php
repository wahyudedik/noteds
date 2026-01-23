<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StoriesCleanupExpired extends Command
{
    protected $signature = 'stories:cleanup-expired {--dry-run : Only report, do not delete}';

    protected $description = 'Remove expired stories and their media files';

    public function handle()
    {
        $expired = Story::where('expires_at', '<=', now())->get();
        $this->info('Expired stories: ' . $expired->count());

        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;

        foreach ($expired as $story) {
            if (!$dryRun) {
                if ($story->media_path && Storage::disk('public')->exists($story->media_path)) {
                    Storage::disk('public')->delete($story->media_path);
                }
                $story->delete();
                $deleted++;
            }
        }

        if ($dryRun) {
            $this->info('Dry run completed.');
        } else {
            $this->info("Deleted {$deleted} stories.");
        }

        return Command::SUCCESS;
    }
}
