<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoriesFollowingFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_following_feed_groups_and_orders_stories()
    {
        $me = User::factory()->create();
        $users = User::factory()->count(10)->create();

        // Mark some as banned
        $users[0]->update(['is_banned' => true]);
        $users[1]->update(['is_banned' => false]);

        // Create follows (exclude banned)
        foreach ($users as $u) {
            Follow::create([
                'follower_id' => $me->id,
                'following_id' => $u->id,
            ]);
        }

        // Unfollow one user
        Follow::where('follower_id', $me->id)->where('following_id', $users[2]->id)->delete();

        // Create stories for each user (2 stories, descending times)
        foreach ($users as $index => $u) {
            // skip banned user stories should not show but still create
            Story::create([
                'user_id' => $u->id,
                'caption' => 's1',
                'media_path' => 'stories/'.$u->id.'/1.jpg',
                'media_type' => 'image',
                'expires_at' => now()->addDay(),
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ]);
            Story::create([
                'user_id' => $u->id,
                'caption' => 's2',
                'media_path' => 'stories/'.$u->id.'/2.jpg',
                'media_type' => 'image',
                'expires_at' => now()->addDay(),
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ]);
        }

        $this->actingAs($me);
        $res = $this->getJson('/stories/following?per_users=5&per_stories_per_user=2');
        $res->assertStatus(200);
        $json = $res->json();

        // Ensure pagination returns 5 user groups
        $this->assertCount(5, $json['data']);

        // Ensure banned user not included
        foreach ($json['data'] as $group) {
            $this->assertNotEquals($users[0]->id, $group['user']['id']);
        }

        // Ensure unfollowed not included
        foreach ($json['data'] as $group) {
            $this->assertNotEquals($users[2]->id, $group['user']['id']);
        }

        // Ensure stories ordered descending by created_at and limited to 2
        foreach ($json['data'] as $group) {
            $stories = $group['stories'];
            $this->assertCount(2, $stories);
            $this->assertTrue($stories[0]['created_at'] >= $stories[1]['created_at']);
        }
    }
}
