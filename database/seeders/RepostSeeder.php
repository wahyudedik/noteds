<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Repost;
use App\Models\Post;
use App\Models\User;

class RepostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::inRandomOrder()->limit(30)->get();
        $users = User::all();
        if ($posts->isEmpty() || $users->isEmpty()) return;

        foreach ($posts as $post) {
            foreach (range(1, rand(0, 3)) as $i) {
                $user = $users->random();
                if ($user->id === $post->user_id) continue;
                Repost::firstOrCreate(
                    ['user_id' => $user->id, 'post_id' => $post->id],
                    [
                        'comment' => rand(0,1) ? 'Menarik, saya repost.' : null,
                        'is_quote_repost' => false,
                    ]
                );
            }
        }
    }
}
