<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;

class PublishScheduledNotes extends Command
{
    protected $signature = 'notes:publish-scheduled';
    protected $description = 'Publish notes scheduled for publishing at or before now';

    public function handle(): int
    {
        $now = now();
        $count = 0;

        $notes = Note::whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', $now)
            ->where(function ($q) {
                $q->where('is_public', false)->orWhere('status', '!=', 'active');
            })
            ->get();

        foreach ($notes as $note) {
            $note->is_public = true;
            $note->status = 'active';
            $note->save();
            $count++;
        }

        $this->info("Published {$count} scheduled notes.");
        return Command::SUCCESS;
    }
}


