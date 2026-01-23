<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $created = $this->faker->dateTimeBetween('-60 days', 'now');
        return [
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => 'active',
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraph(3),
            'purpose_type' => $this->faker->randomElement(['idea_business', 'validate_idea', 'find_tools']),
            'upvotes_count' => $this->faker->numberBetween(0, 500),
            'downvotes_count' => $this->faker->numberBetween(0, 200),
            'comments_count' => $this->faker->numberBetween(0, 300),
            'reposts_count' => $this->faker->numberBetween(0, 150),
            'total_views' => $this->faker->numberBetween(0, 10000),
            'created_at' => $created,
            'updated_at' => $created,
            'publish_status' => 'published',
        ];
    }

    public function recent(): self
    {
        return $this->state(function () {
            $created = now()->subHours(rand(1, 24));
            return ['created_at' => $created, 'updated_at' => $created];
        });
    }

    public function old(): self
    {
        return $this->state(function () {
            $created = now()->subDays(rand(30, 90));
            return ['created_at' => $created, 'updated_at' => $created];
        });
    }
}
