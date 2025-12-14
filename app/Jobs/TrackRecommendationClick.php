<?php

namespace App\Jobs;

use App\Models\RecommendationClick;
use App\Models\RecommendationImpression;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackRecommendationClick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $impressionId,
        public ?string $userId,
        public string $noteId,
        public string $context
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            RecommendationClick::create([
                'impression_id' => $this->impressionId,
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
                'context' => $this->context,
            ]);

            Log::info('Recommendation click tracked', [
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
                'context' => $this->context,
                'impression_id' => $this->impressionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track recommendation click', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
            ]);

            throw $e;
        }
    }
}
