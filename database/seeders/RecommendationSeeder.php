<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Recommendation;
use App\Models\RecommendationClick;
use App\Models\RecommendationImpression;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users and notes
        $users = User::active()->limit(10)->get();
        $notes = Note::where('status', 'active')
            ->where('is_public', true)
            ->limit(50)
            ->get();

        if ($users->isEmpty() || $notes->isEmpty()) {
            $this->command->warn('Not enough users or notes to seed recommendations');
            return;
        }

        $algorithms = [
            Recommendation::ALGORITHM_COLLABORATIVE,
            Recommendation::ALGORITHM_CONTENT_BASED,
            Recommendation::ALGORITHM_TRENDING,
            Recommendation::ALGORITHM_PROFILE_BASED,
        ];

        $contexts = [
            RecommendationImpression::CONTEXT_HOMEPAGE,
            RecommendationImpression::CONTEXT_MARKETPLACE,
            RecommendationImpression::CONTEXT_SIMILAR_NOTES,
            RecommendationImpression::CONTEXT_PROFILE,
        ];

        foreach ($users as $user) {
            // Create recommendations for each user
            $userNotes = $notes->random(min(15, $notes->count()));

            foreach ($userNotes as $index => $note) {
                Recommendation::create([
                    'user_id' => $user->id,
                    'note_id' => $note->id,
                    'algorithm' => $algorithms[array_rand($algorithms)],
                    'score' => rand(50, 100) / 10, // 5.0 to 10.0
                    'metadata' => [
                        'relevance_factors' => [
                            'category_match' => rand(0, 100),
                            'tag_similarity' => rand(0, 100),
                            'rating' => rand(30, 50) / 10,
                        ],
                    ],
                    'created_at' => now(),
                ]);
            }

            // Create impressions (shown to user)
            $impressionNotes = $userNotes->random(min(10, $userNotes->count()));

            foreach ($impressionNotes as $position => $note) {
                $impression = RecommendationImpression::create([
                    'user_id' => $user->id,
                    'note_id' => $note->id,
                    'context' => $contexts[array_rand($contexts)],
                    'algorithm' => $algorithms[array_rand($algorithms)],
                    'position' => $position,
                    'created_at' => now(),
                ]);

                // 30% of impressions result in clicks
                if (rand(1, 100) <= 30) {
                    RecommendationClick::create([
                        'impression_id' => $impression->id,
                        'user_id' => $user->id,
                        'note_id' => $note->id,
                        'context' => $impression->context,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Recommendation data seeded successfully!');
        $this->command->info('Total recommendations: ' . Recommendation::count());
        $this->command->info('Total impressions: ' . RecommendationImpression::count());
        $this->command->info('Total clicks: ' . RecommendationClick::count());

        // Calculate and display CTR
        foreach ($contexts as $context) {
            $ctr = RecommendationClick::calculateCTR($context, 30);
            $this->command->info("CTR for {$context}: " . number_format($ctr, 2) . '%');
        }
    }
}
