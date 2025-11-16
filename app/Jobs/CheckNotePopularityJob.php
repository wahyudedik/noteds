<?php

namespace App\Jobs;

use App\Models\Note;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckNotePopularityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $noteId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $note = Note::find($this->noteId);
            
            if (!$note) {
                Log::warning('Note not found for popularity check', [
                    'note_id' => $this->noteId,
                ]);
                return;
            }

            $thresholds = $notificationService->getPopularityThresholds();
            $purchaseCount = $note->purchase_count;

            foreach ($thresholds as $threshold) {
                if ($purchaseCount >= $threshold) {
                    // Check if we've already notified for this threshold
                    $cacheKey = "note_popularity_notified_{$note->id}_{$threshold}";
                    
                    if (!cache()->has($cacheKey)) {
                        $notificationService->notifyNotePopular($note, $threshold);
                        cache()->put($cacheKey, true, now()->addDays(30));
                        
                        Log::info('Note popularity milestone reached', [
                            'note_id' => $note->id,
                            'threshold' => $threshold,
                            'purchase_count' => $purchaseCount,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Note popularity check job failed', [
                'note_id' => $this->noteId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

