<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\User;

class BookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->get();
        $posts = Post::inRandomOrder()->limit(50)->get();
        foreach ($users as $u) {
            $pick = $posts->shuffle()->take(rand(3, 8));
            foreach ($pick as $p) {
                $exists = DB::table('bookmarks')->where('user_id', $u->id)->where('post_id', $p->id)->exists();
                if (!$exists) {
                    DB::table('bookmarks')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'user_id' => $u->id,
                        'post_id' => $p->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
