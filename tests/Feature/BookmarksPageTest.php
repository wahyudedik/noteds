<?php

namespace Tests\Feature;

use App\Models\Bookmark;
use App\Models\BookmarkTag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmarks_page_loads_with_tags(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $bookmark = Bookmark::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'collection_id' => null,
        ]);
        $tag = BookmarkTag::create([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'user_id' => $user->id,
            'is_global' => false,
            'usage_count' => 0,
        ]);
        $bookmark->addTag($tag);

        $resp = $this->actingAs($user)->get('/bookmarks');

        $resp->assertStatus(200);
        $resp->assertSee('Bookmarks\\/Index');
    }
}
