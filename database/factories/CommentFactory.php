<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        return [
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => null,
            'content' => $this->faker->paragraph(2),
            'upvotes_count' => 0,
            'downvotes_count' => 0,
            'weighted_upvotes_score' => 0,
            'weighted_downvotes_score' => 0,
            'is_best_answer' => false,
            'edit_count' => 0,
            'is_pinned' => false,
        ];
    }
}
