<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationClickRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_as_read_redirects_to_target_route()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $post = Post::factory()->create();
        $n = DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\PostModeratedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'type' => 'post_moderated',
                'post_id' => $post->id,
                'title' => 'Post Moderated',
                'message' => 'Your post was moderated',
            ],
        ]);
        $resp = $this->post(route('notifications.read', $n->id));
        $resp->assertStatus(302);
        $this->assertNotNull($user->notifications()->find($n->id)->read_at);
    }
}
