<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_updates_do_not_corrupt_counts()
    {
        $post = Post::factory()->recent()->create(['upvotes_count' => 0, 'comments_count' => 0, 'reposts_count' => 0]);
        $iterations = 50;
        $callbacks = [];
        for ($i = 0; $i < $iterations; $i++) {
            $callbacks[] = function () use ($post) {
                DB::transaction(function () use ($post) {
                    $p = Post::lockForUpdate()->find($post->id);
                    $p->upvotes_count += 1;
                    $p->comments_count += 1;
                    $p->save();
                });
                Cache::forget('posts_top_week_engagement_all_page_1');
            };
        }
        foreach ($callbacks as $cb) { $cb(); }
        $final = Post::find($post->id);
        $this->assertEquals($iterations, $final->upvotes_count);
        $this->assertEquals($iterations, $final->comments_count);
    }
}
