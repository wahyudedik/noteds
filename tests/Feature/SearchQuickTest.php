<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchQuickTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_search_returns_users_and_posts(): void
    {
        $viewer = User::factory()->create();
        $matchedUser = User::factory()->create([
            'name' => 'Alpha Beta',
            'is_banned' => false,
        ]);
        $post = Post::factory()->create([
            'title' => 'Alpha Post',
            'content' => 'Something',
            'status' => 'active',
            'user_id' => $matchedUser->id,
        ]);

        $resp = $this->actingAs($viewer)->get('/search/quick?q=alpha');

        $resp->assertStatus(200);
        $resp->assertJsonStructure([
            'users',
            'posts',
        ]);
        $resp->assertJsonFragment(['id' => $matchedUser->id]);
        $resp->assertJsonFragment(['id' => $post->id]);
    }
}

