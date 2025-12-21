<?php

namespace Database\Seeders;

use App\Models\IdeaValidation;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class IdeaValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all validate_idea posts
        $validatePosts = Post::where('purpose_type', 'validate_idea')->get();
        $users = User::all();

        foreach ($validatePosts as $post) {
            // Get 3-8 users to validate this idea
            $numValidators = rand(3, 8);
            $validators = $users->random(min($numValidators, $users->count()));

            foreach ($validators as $user) {
                // Skip if user is the post owner
                if ($user->id === $post->user_id) {
                    continue;
                }

                // 70% layak, 30% tidak layak
                $isLayak = rand(1, 10) <= 7;
                $validationStatus = $isLayak ? 'layak' : 'tidak_layak';

                $estimatedCapital = $isLayak
                    ? rand(50000000, 500000000) // 50M - 500M
                    : rand(100000000, 1000000000); // 100M - 1B (higher if not layak)

                $estimatedBep = $isLayak
                    ? rand(6, 24) // 6-24 months
                    : rand(24, 60); // 24-60 months (longer if not layak)

                $feedback = $isLayak
                    ? $this->getPositiveFeedback()
                    : $this->getNegativeFeedback();

                $risks = $this->getRisks($isLayak);

                IdeaValidation::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'validation_status' => $validationStatus,
                    'estimated_capital' => $estimatedCapital,
                    'estimated_bep' => $estimatedBep,
                    'feedback' => $feedback,
                    'risks' => $risks,
                ]);
            }
        }
    }

    private function getPositiveFeedback(): string
    {
        $feedbacks = [
            'Ide ini sangat menjanjikan dengan market potential yang besar. Perlu fokus pada execution dan marketing strategy.',
            'Konsepnya solid dan ada demand yang jelas. Rekomendasi: validate dengan early adopters sebelum full launch.',
            'Market opportunity-nya bagus. Perlu perhatikan competition dan differentiation strategy.',
            'Ide yang inovatif! Perlu pertimbangkan pricing strategy dan unit economics yang jelas.',
        ];

        return $feedbacks[array_rand($feedbacks)];
    }

    private function getNegativeFeedback(): string
    {
        $feedbacks = [
            'Ide ini menarik tapi market sudah cukup saturated. Perlu unique value proposition yang lebih kuat.',
            'Konsepnya bagus tapi monetization strategy perlu diperjelas. Revenue model masih kurang jelas.',
            'Market size mungkin terlalu kecil untuk sustainable business. Perlu expand target market.',
            'Execution akan sangat challenging. Perlu pertimbangkan resource dan capability yang dimiliki.',
        ];

        return $feedbacks[array_rand($feedbacks)];
    }

    private function getRisks(bool $isLayak): array
    {
        if ($isLayak) {
            return [
                'Competition dari established players',
                'Market adoption rate',
                'Regulatory changes',
            ];
        } else {
            return [
                'High competition',
                'Limited market size',
                'High customer acquisition cost',
                'Unclear monetization',
                'Execution complexity',
            ];
        }
    }
}
