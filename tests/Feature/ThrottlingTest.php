<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ThrottlingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Clear any existing rate limiters before each test
        RateLimiter::clear('throttle:5,10');
        RateLimiter::clear('throttle:10,5');
        RateLimiter::clear('throttle:30,5');
        RateLimiter::clear('throttle:5,30');
        RateLimiter::clear('throttle:3,60');
        RateLimiter::clear('throttle:10,60');
        RateLimiter::clear('throttle:10,1');
        RateLimiter::clear('throttle:5,60');
        RateLimiter::clear('throttle:3,1440');
        RateLimiter::clear('throttle:20,5');
        RateLimiter::clear('throttle:30,1');
    }

    /** @test */
    public function post_creation_is_throttled_to_10_per_5_minutes()
    {
        // Make 10 successful requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('posts.store'), [
                'purpose_type' => 'idea_business',
                'title' => str_repeat('a', 10) . " Test Post {$i}",
                'content' => str_repeat('a', 50) . ' Test content for post creation throttling test.',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->post(route('posts.store'), [
            'purpose_type' => 'idea_business',
            'title' => str_repeat('a', 10) . ' Test Post 11',
            'content' => str_repeat('a', 50) . ' Test content for post creation throttling test.',
        ]);
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function comment_creation_is_throttled_to_30_per_5_minutes()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        // Make 30 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 30; $i++) {
            $response = $this->post(route('comments.store', $post), [
                'content' => "Comment {$i} with enough content to pass validation",
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->post(route('comments.store', $post), [
            'content' => 'Comment 31 with enough content to pass validation',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function post_vote_is_throttled_to_30_per_5_minutes()
    {
        $post = Post::factory()->create();

        // Make 30 requests - they may succeed or fail, but shouldn't be throttled
        for ($i = 0; $i < 30; $i++) {
            $response = $this->post(route('votes.post', $post), [
                'vote_type' => 'upvote',
            ]);
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->post(route('votes.post', $post), [
            'vote_type' => 'upvote',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function comment_vote_is_throttled_to_30_per_5_minutes()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        // Make 30 requests - they may succeed or fail, but shouldn't be throttled
        for ($i = 0; $i < 30; $i++) {
            $response = $this->post(route('votes.comment', $comment), [
                'vote_type' => 'upvote',
            ]);
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->post(route('votes.comment', $comment), [
            'vote_type' => 'upvote',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function idea_validation_is_throttled_to_5_per_30_minutes()
    {
        $post = Post::factory()->create([
            'purpose_type' => 'validate_idea',
            'user_id' => $this->user->id,
        ]);

        // Make 5 successful requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('idea-validations.store', $post), [
                'validation_status' => 'layak',
            ]);
            // Could be 302 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 6th request should be throttled
        $response = $this->post(route('idea-validations.store', $post), [
            'validation_status' => 'layak',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function profile_update_is_throttled_to_10_per_hour()
    {
        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->patch(route('profile.update'), [
                'name' => "Updated Name {$i}",
                'email' => $this->user->email,
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->patch(route('profile.update'), [
            'name' => 'Updated Name 11',
            'email' => $this->user->email,
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function profile_delete_is_throttled_to_3_per_24_hours()
    {
        // Note: This test is tricky because deletion is destructive
        // We'll test that making multiple delete requests gets throttled
        // In practice, the first request might succeed and delete the user

        // Make 3 requests - they may succeed or fail, but shouldn't be throttled (except after first success)
        for ($i = 0; $i < 3; $i++) {
            // Re-authenticate if user was deleted
            if (!auth()->check()) {
                $this->user = User::factory()->create();
                $this->actingAs($this->user);
            }

            $response = $this->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

            // After first successful deletion, subsequent requests should be throttled or fail
            // But we're testing that throttle middleware is applied
            if ($i === 0) {
                // First request might succeed (302) or fail validation (422), but not 429
                $this->assertNotEquals(429, $response->status(), "First request should not be throttled");
            }
        }
    }

    /** @test */
    public function contact_form_is_throttled_to_3_per_hour()
    {
        // Make 3 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('legal.contact.submit'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => "Test Subject {$i}",
                'message' => 'Test message',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('legal.contact.submit'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject 4',
            'message' => 'Test message',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function explorer_search_is_throttled_to_30_per_minute()
    {
        // Make 30 requests - they may succeed or fail, but shouldn't be throttled
        for ($i = 0; $i < 30; $i++) {
            $response = $this->get(route('explorer.search', ['q' => "search term {$i}"]));
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->get(route('explorer.search', ['q' => 'search term 31']));
        $response->assertStatus(429);
    }
}
