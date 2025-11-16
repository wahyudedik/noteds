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

        // Support both scheduled_at (new) and scheduled_publish_at (old) fields
        $scheduledNotes = Note::where(function ($q) {
                $q->whereNotNull('scheduled_at')
                  ->orWhereNotNull('scheduled_publish_at');
            })
            ->where(function ($q) {
                $q->where('scheduled_at', '<=', now())
                  ->orWhere('scheduled_publish_at', '<=', now());
            })
            ->where('is_draft', false)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere(function ($q2) {
                      $q2->where('is_public', false)->orWhere('status', '!=', 'active');
                  });
            })
            ->get();

        if ($scheduledNotes->isEmpty()) {
            $this->info('No scheduled notes to publish.');
            return Command::SUCCESS;
        }

        $this->info("Found {$scheduledNotes->count()} note(s) to publish.");

        $published = 0;
        foreach ($scheduledNotes as $note) {
            try {
                $updateData = [
                    'status' => 'active',
                    'is_public' => true,
                ];

                // Handle both field names
                if ($note->scheduled_at) {
                    $updateData['published_at'] = now();
                    $updateData['scheduled_at'] = null;
                }
                if ($note->scheduled_publish_at) {
                    $updateData['scheduled_publish_at'] = null;
                }

                $note->update($updateData);

                // Notify if public
                if ($note->is_public && method_exists($note, 'notificationMeta') && !$note->notificationMeta('published_notified_at')) {
                    $notificationService->notifyNewNotePublished($note);
                    if (method_exists($note, 'setNotificationMetaValue')) {
                        $note->setNotificationMetaValue('published_notified_at', now()->toIso8601String());
                    }
                }

                // Create history record if model exists
                if (class_exists(\App\Models\NoteHistory::class)) {
                    \App\Models\NoteHistory::create([
                        'note_id' => $note->id,
                        'user_id' => $note->user_id,
                        'action' => 'published',
                        'old_data' => ['scheduled_at' => $note->scheduled_at, 'scheduled_publish_at' => $note->scheduled_publish_at],
                        'new_data' => ['published_at' => now()],
                        'changes' => 'Note published (scheduled)',
                        'notes' => 'Note automatically published from scheduled time',
                    ]);
                }

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
