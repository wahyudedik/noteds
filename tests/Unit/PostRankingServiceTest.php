<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Services\PostRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PostRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_engagement_metric_orders_by_comments_and_reposts_with_time_decay()
    {
        Post::factory()->count(10)->old()->create(['comments_count' => 5, 'reposts_count' => 1]);
        $topRecent = Post::factory()->recent()->create(['comments_count' => 20, 'reposts_count' => 5]);
        $svc = app(PostRankingService::class);
        $res = $svc->getTopPosts('week', 'engagement', 15);
        $ids = $res->pluck('id')->all();
        $this->assertEquals($topRecent->id, $ids[0]);
    }

    public function test_upvotes_metric_considers_votes_comments_reposts()
    {
        $p1 = Post::factory()->recent()->create(['upvotes_count' => 100, 'downvotes_count' => 10, 'comments_count' => 5, 'reposts_count' => 1]);
        $p2 = Post::factory()->recent()->create(['upvotes_count' => 80, 'downvotes_count' => 0, 'comments_count' => 20, 'reposts_count' => 10]);
        $svc = app(PostRankingService::class);
        $res = $svc->getTopPosts('day', 'upvotes', 15);
        $ids = $res->pluck('id')->all();
        $this->assertContains($p1->id, $ids);
        $this->assertContains($p2->id, $ids);
    }

    public function test_period_filter_limits_posts_by_created_at()
    {
        Post::factory()->old()->count(5)->create(['comments_count' => 100, 'reposts_count' => 50]);
        $recent = Post::factory()->recent()->count(3)->create(['comments_count' => 2, 'reposts_count' => 1]);
        $svc = app(PostRankingService::class);
        $week = $svc->getTopPosts('week', 'engagement', 50);
        $this->assertEquals(3, $week->count());
    }

    public function test_caching_returns_same_page_quickly()
    {
        Post::factory()->recent()->count(10)->create();
        $svc = app(PostRankingService::class);
        request()->merge(['page' => 1]);
        $cacheKey = sprintf('posts_top_%s_%s_%s_page_%s', 'week', 'engagement', 'all', request('page', 1));

        $first = $svc->getTopPosts('week', 'engagement', 15);
        $this->assertTrue(Cache::has($cacheKey));

        $second = $svc->getTopPosts('week', 'engagement', 15);
        $this->assertEquals(
            $first->getCollection()->pluck('id')->all(),
            $second->getCollection()->pluck('id')->all()
        );
    }
}
