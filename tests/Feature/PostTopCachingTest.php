<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Services\PostRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PostTopCachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_invalidation_by_flush_reflects_updated_scores()
    {
        Cache::flush();
        $createdAt = now()->subHour();
        $p1 = Post::factory()->create([
            'upvotes_count' => 10,
            'downvotes_count' => 0,
            'comments_count' => 1,
            'reposts_count' => 0,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $p2 = Post::factory()->create([
            'upvotes_count' => 5,
            'downvotes_count' => 0,
            'comments_count' => 1,
            'reposts_count' => 0,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $svc = app(PostRankingService::class);
        $first = $svc->getTopPosts('day', 'upvotes', 15);
        $topId = $first->getCollection()->pluck('id')->first();
        $this->assertEquals($p1->id, $topId);
        $p2->update(['upvotes_count' => 100]);
        $svc->getTopPosts('day', 'upvotes', 15); // cached
        Cache::flush(); // simulate invalidation after write
        $second = $svc->getTopPosts('day', 'upvotes', 15);
        $this->assertEquals($p2->id, $second->getCollection()->pluck('id')->first());
    }

    public function test_cache_separates_by_params_page_and_filters()
    {
        Cache::flush();
        Post::factory()->recent()->count(40)->create(['purpose_type' => 'idea_business']);
        Post::factory()->recent()->count(40)->create(['purpose_type' => 'validate_idea']);
        $svc = app(PostRankingService::class);
        request()->merge(['page' => 1]);
        $res1 = $svc->getTopPosts('week', 'engagement', 15, 'idea_business');
        request()->merge(['page' => 2]);
        $res2 = $svc->getTopPosts('week', 'engagement', 15, 'idea_business');
        $this->assertNotEquals($res1->getCollection()->pluck('id')->first(), $res2->getCollection()->pluck('id')->first());
        request()->merge(['page' => 1]);
        $res3 = $svc->getTopPosts('week', 'engagement', 15, 'validate_idea');
        $this->assertNotEquals($res1->getCollection()->pluck('id')->first(), $res3->getCollection()->pluck('id')->first());
    }
}
