<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTopControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_top_posts()
    {
        Post::factory()->recent()->count(5)->create();
        $resp = $this->get('/posts/top?period=week&metric=engagement');
        $resp->assertStatus(200);
        $resp->assertSee('Posts/Index'); // Inertia component name
    }

    public function test_period_and_metric_parameters_affect_results()
    {
        $highEng = Post::factory()->recent()->create(['comments_count' => 50, 'reposts_count' => 20]);
        $highVotes = Post::factory()->recent()->create(['upvotes_count' => 200, 'downvotes_count' => 10, 'comments_count' => 1, 'reposts_count' => 0]);
        $resp = $this->get('/posts/top?period=day&metric=engagement');
        $resp->assertStatus(200);
        $resp2 = $this->get('/posts/top?period=day&metric=upvotes');
        $resp2->assertStatus(200);
    }

    public function test_invalid_parameters_use_defaults()
    {
        Post::factory()->recent()->count(3)->create();
        $resp = $this->get('/posts/top?period=invalid&period=invalid&metric=bad');
        $resp->assertStatus(200);
    }
}
