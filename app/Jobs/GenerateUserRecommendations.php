<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateUserRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $userId,
        public int $limit = 50
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RecommendationService $recommendationService): void
    {
        try {
            $user = User::findOrFail($this->userId);

            // Clear old recommendations (keep last 7 days)
            Recommendation::where('user_id', $this->userId)
                ->where('created_at', '<', now()->subDays(7))
                ->delete();

            // Generate new recommendations
            $recommendations = $recommendationService->getRecommendations($user, $this->limit);

            // Store recommendations with metadata
            $records = [];
            foreach ($recommendations as $index => $note) {
                if (!$note || !isset($note['id'])) {
                    continue;
                }

                $records[] = [
                    'user_id' => $this->userId,
                    'note_id' => $note['id'],
                    'algorithm' => $note['algorithm'] ?? Recommendation::ALGORITHM_CONTENT_BASED,
                    'score' => $note['score'] ?? 0,
                    'metadata' => json_encode($note['metadata'] ?? []),
                    'created_at' => now(),
                ];
            }

            if (!empty($records)) {
                DB::table('recommendations')->insert($records);

                Log::info('Generated recommendations for user', [
                    'user_id' => $this->userId,
                    'count' => count($records),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate recommendations', [
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
