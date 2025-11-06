<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeaturedNote>
 */
class FeaturedNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note_id' => \App\Models\Note::factory(),
            'user_id' => \App\Models\User::factory(),
            'location' => fake()->randomElement(['landing_hero', 'landing_carousel', 'marketplace_banner', 'marketplace_grid', 'popup_welcome', 'popup_exit', 'popup_interstitial']),
            'duration_days' => fake()->randomElement([7, 14, 30]),
            'price' => fake()->randomFloat(2, 50000, 500000),
            'status' => 'pending',
            'clicks' => 0,
            'impressions' => 0,
            'discount_percent' => 0,
            'is_custom_duration' => false,
        ];
    }
}
