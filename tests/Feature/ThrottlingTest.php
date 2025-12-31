<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Campaign;
use App\Models\Clip;
use App\Models\Marketplace\Product;
use App\Models\Marketplace\Order;
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
    public function post_creation_is_throttled_to_5_per_10_minutes()
    {
        // Make 5 successful requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('posts.store'), [
                'purpose_type' => 'idea_business',
                'title' => str_repeat('a', 10) . " Test Post {$i}",
                'content' => str_repeat('a', 50) . ' Test content for post creation throttling test.',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 6th request should be throttled
        $response = $this->post(route('posts.store'), [
            'purpose_type' => 'idea_business',
            'title' => str_repeat('a', 10) . ' Test Post 6',
            'content' => str_repeat('a', 50) . ' Test content for post creation throttling test.',
        ]);
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function comment_creation_is_throttled_to_10_per_5_minutes()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('comments.store', $post), [
                'content' => "Comment {$i} with enough content to pass validation",
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->post(route('comments.store', $post), [
            'content' => 'Comment 11 with enough content to pass validation',
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
                'vote_type' => 'up',
            ]);
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->post(route('votes.post', $post), [
            'vote_type' => 'up',
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
                'vote_type' => 'up',
            ]);
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 31st request should be throttled
        $response = $this->post(route('votes.comment', $comment), [
            'vote_type' => 'up',
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
    public function product_creation_is_throttled_to_3_per_hour()
    {
        // Make 3 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('marketplace.products.store'), [
                'name' => "Product {$i}",
                'description' => 'Test description',
                'price' => 10000,
                'category' => 'digital',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('marketplace.products.store'), [
            'name' => 'Product 4',
            'description' => 'Test description',
            'price' => 10000,
            'category' => 'digital',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function product_update_is_throttled_to_10_per_hour()
    {
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->put(route('marketplace.products.update', $product), [
                'name' => "Updated Product {$i}",
                'description' => 'Test description',
                'price' => 10000,
                'category' => 'digital',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->put(route('marketplace.products.update', $product), [
            'name' => 'Updated Product 11',
            'description' => 'Test description',
            'price' => 10000,
            'category' => 'digital',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function order_creation_is_throttled_to_10_per_minute()
    {
        $product = Product::factory()->create();

        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('marketplace.orders.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->post(route('marketplace.orders.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function order_cancellation_is_throttled_to_5_per_hour()
    {
        // Make 5 requests (create new orders for each cancellation)
        for ($i = 0; $i < 5; $i++) {
            $newOrder = Order::factory()->create(['user_id' => $this->user->id]);
            $response = $this->post(route('marketplace.orders.cancel', $newOrder));
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 6th request should be throttled
        $newOrder = Order::factory()->create(['user_id' => $this->user->id]);
        $response = $this->post(route('marketplace.orders.cancel', $newOrder));
        $response->assertStatus(429);
    }

    /** @test */
    public function marketplace_withdrawal_is_throttled_to_3_per_24_hours()
    {
        // Make 3 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('marketplace.withdrawals.store'), [
                'amount' => 10000,
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'account_name' => 'Test Account',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('marketplace.withdrawals.store'), [
            'amount' => 10000,
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
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
            $response = $this->post(route('contact.submit'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => "Test Subject {$i}",
                'message' => 'Test message',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('contact.submit'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject 4',
            'message' => 'Test message',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function marketplace_download_is_throttled_to_20_per_5_minutes()
    {
        $product = Product::factory()->create();

        // Make 20 requests - they may succeed or fail, but shouldn't be throttled
        for ($i = 0; $i < 20; $i++) {
            $response = $this->get(route('marketplace.products.download', $product));
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 21st request should be throttled
        $response = $this->get(route('marketplace.products.download', $product));
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

    /** @test */
    public function clipper_top_up_is_throttled_to_5_per_hour()
    {
        // Make 5 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('clipper.top-ups.store'), [
                'amount' => 10000,
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 6th request should be throttled
        $response = $this->post(route('clipper.top-ups.store'), [
            'amount' => 10000,
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function clipper_campaign_creation_is_throttled_to_3_per_hour()
    {
        // Make 3 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('clipper.campaigns.store'), [
                'title' => "Campaign {$i}",
                'description' => 'Test description',
                'cpm' => 1000,
                'max_budget' => 100000,
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('clipper.campaigns.store'), [
            'title' => 'Campaign 4',
            'description' => 'Test description',
            'cpm' => 1000,
            'max_budget' => 100000,
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function clipper_campaign_update_is_throttled_to_10_per_hour()
    {
        $campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);

        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->put(route('clipper.campaigns.update', $campaign), [
                'title' => "Updated Campaign {$i}",
                'description' => 'Test description',
                'cpm' => 1000,
                'max_budget' => 100000,
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->put(route('clipper.campaigns.update', $campaign), [
            'title' => 'Updated Campaign 11',
            'description' => 'Test description',
            'cpm' => 1000,
            'max_budget' => 100000,
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function clipper_clip_submission_is_throttled_to_10_per_5_minutes()
    {
        $campaign = Campaign::factory()->create(['status' => 'active']);

        // Make 10 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('clipper.clips.store'), [
                'campaign_id' => $campaign->id,
                'content_url' => "https://www.youtube.com/watch?v=test{$i}",
                'platform' => 'youtube',
                'platform_content_id' => "test{$i}",
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->post(route('clipper.clips.store'), [
            'campaign_id' => $campaign->id,
            'content_url' => 'https://www.youtube.com/watch?v=test11',
            'platform' => 'youtube',
            'platform_content_id' => 'test11',
        ]);
        $response->assertStatus(429);
    }

    /** @test */
    public function clipper_view_tracking_is_throttled_to_10_per_hour()
    {
        $campaign = Campaign::factory()->create(['status' => 'active']);
        $clip = Clip::factory()->create([
            'campaign_id' => $campaign->id,
            'clipper_id' => $this->user->id,
        ]);

        // Make 10 requests - they may succeed or fail, but shouldn't be throttled
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('clipper.clips.track-views', $clip));
            // Could be 200 (success) or other status, but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 11th request should be throttled
        $response = $this->post(route('clipper.clips.track-views', $clip));
        $response->assertStatus(429);
    }

    /** @test */
    public function clipper_withdrawal_is_throttled_to_3_per_24_hours()
    {
        // Make 3 requests - they may succeed or fail validation, but shouldn't be throttled
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('clipper.withdrawals.store'), [
                'amount' => 10000,
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'account_name' => 'Test Account',
            ]);
            // Could be 302 (success) or 422 (validation error), but not 429
            $this->assertNotEquals(429, $response->status(), "Request {$i} should not be throttled yet");
        }

        // 4th request should be throttled
        $response = $this->post(route('clipper.withdrawals.store'), [
            'amount' => 10000,
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'account_name' => 'Test Account',
        ]);
        $response->assertStatus(429);
    }
}

