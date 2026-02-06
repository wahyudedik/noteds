<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_loads(): void
    {
        $user = User::factory()->create();
        Post::factory()->create([
            'title' => 'Hello Search',
            'content' => 'Hello world',
            'status' => 'active',
        ]);

        $resp = $this->actingAs($user)->get('/search?q=hello');

        $resp->assertStatus(200);
        $resp->assertSee('Search\\/Index');
    }

    public function test_search_products_type_is_accepted_for_backward_compatibility(): void
    {
        $user = User::factory()->create();

        $resp = $this->actingAs($user)->get('/search?q=test&type=products');

        $resp->assertStatus(200);
        $resp->assertSee('Search\\/Index');
    }
}

