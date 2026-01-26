<?php

namespace Tests\Unit;

use App\Events\PostReposted;
use App\Jobs\TrackRepostAnalytics;
use App\Listeners\TrackRepostAnalyticsListener;
use App\Models\Repost;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class TrackRepostAnalyticsListenerTest extends TestCase
{
    public function test_handle_dispatches_job_with_repost_model()
    {
        Bus::fake();

        // Create a mock Repost
        $repost = \Mockery::mock(Repost::class);
        
        // Instantiate the event with the mocked Repost
        $event = new PostReposted($repost);

        // Instantiate the listener
        $listener = new TrackRepostAnalyticsListener();
        
        // Call handle
        $listener->handle($event);

        // Assert the job was dispatched with the correct Repost instance
        Bus::assertDispatched(TrackRepostAnalytics::class, function ($job) use ($repost) {
            return $job->repost === $repost;
        });
    }
}
