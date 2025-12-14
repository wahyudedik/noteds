<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Models\RecommendationImpression;
use App\Models\User;
use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackRecommendationImpression implements ShouldQueue
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
        public ?string $userId,
        public string $noteId,
        public string $context,
        public ?string $algorithm = null,
        public int $position = 0
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            RecommendationImpression::create([
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
                'context' => $this->context,
                'algorithm' => $this->algorithm,
                'position' => $this->position,
            ]);

            Log::info('Recommendation impression tracked', [
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
                'context' => $this->context,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track recommendation impression', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'note_id' => $this->noteId,
            ]);

            throw $e;
        }
    }
}
