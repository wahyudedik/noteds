<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class PublishScheduledNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish notes that are scheduled for publishing';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for scheduled notes...');

        $scheduledNotes = Note::whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->where('is_draft', false)
            ->whereNull('published_at')
            ->get();

        if ($scheduledNotes->isEmpty()) {
            $this->info('No scheduled notes to publish.');
            return Command::SUCCESS;
        }

        $this->info("Found {$scheduledNotes->count()} note(s) to publish.");

        $published = 0;
        foreach ($scheduledNotes as $note) {
            try {
                $note->update([
                    'published_at' => now(),
                    'scheduled_at' => null,
                    'status' => 'active',
                ]);

                // Notify if public
                if ($note->is_public && !$note->notificationMeta('published_notified_at')) {
                    $notificationService->notifyNewNotePublished($note);
                    $note->setNotificationMetaValue('published_notified_at', now()->toIso8601String());
                }

                // Create history record
                \App\Models\NoteHistory::create([
                    'note_id' => $note->id,
                    'user_id' => $note->user_id,
                    'action' => 'published',
                    'old_data' => ['scheduled_at' => $note->scheduled_at],
                    'new_data' => ['published_at' => now()],
                    'changes' => 'Note published (scheduled)',
                    'notes' => 'Note automatically published from scheduled time',
                ]);

                $published++;
                $this->info("✓ Published: {$note->title}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to publish note {$note->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully published {$published} note(s).");

        return Command::SUCCESS;
    }
}
