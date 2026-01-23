<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;

class HashtagSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['startup','laravel','marketing','design','funding','ai','product','growth','community','tools'];
        $hashIds = [];
        foreach ($names as $n) {
            $hash = Hashtag::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($n)], ['name' => $n]);
            $hashIds[] = $hash->id;
        }

        // Attach hashtags to recent posts
        $posts = Post::inRandomOrder()->limit(60)->get();
        foreach ($posts as $post) {
            $attach = collect($hashIds)->shuffle()->take(rand(1,3))->all();
            $post->hashtags()->syncWithoutDetaching($attach);
        }

        // Update posts_count
        foreach ($hashIds as $hid) {
            $h = Hashtag::find($hid);
            if ($h) {
                $h->posts_count = $h->posts()->count();
                $h->save();
            }
        }
    }
}
