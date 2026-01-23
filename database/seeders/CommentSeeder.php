<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::inRandomOrder()->limit(25)->get();
        $users = User::all();
        if ($posts->isEmpty() || $users->isEmpty()) return;

        foreach ($posts as $post) {
            foreach (range(1, rand(2, 6)) as $i) {
                $author = $users->random();
                $comment = Comment::create([
                    'user_id' => $author->id,
                    'post_id' => $post->id,
                    'content' => 'Komentar contoh #' . $i . ' untuk pengujian fitur.',
                ]);
                if (rand(0,1) === 1) {
                    Comment::create([
                        'user_id' => $users->random()->id,
                        'post_id' => $post->id,
                        'parent_id' => $comment->id,
                        'content' => 'Balasan untuk komentar #' . $i,
                    ]);
                }
            }
        }
    }
}
