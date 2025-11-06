<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'summary' => fake()->sentence(),
            'preview_content' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 0, 100000),
            'is_public' => fake()->boolean(),
            'status' => 'active',
            'is_sold' => false,
            'attachments' => [],
            'file_count' => 0,
        ];
    }
}
