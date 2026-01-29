<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class RateLimiterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stress_search_limiter_blocks_second_request()
    {
        $this->actingAs(User::factory()->create());
        Cache::put('rate_limit:search', ['limit' => 1, 'duration' => 'minute'], 600);
        $this->getJson('/search/suggestions?q=test')->assertStatus(200);
        $this->getJson('/search/suggestions?q=test')->assertStatus(429);
    }

    public function test_webhook_alert_at_80_percent()
    {
        config(['ratelimit.alert_thresholds.per_endpoint_per_minute' => 5]);
        config(['ratelimit.alert_thresholds.webhook' => 'http://example.com/webhook']);
        Http::fake([
            'http://example.com/webhook' => Http::response(['status' => 'ok'], 200),
        ]);
        $handler = app(\App\Exceptions\Handler::class);
        for ($i = 0; $i < 4; $i++) {
            $req = \Illuminate\Http\Request::create('/search/suggestions', 'GET', ['q' => 'x']);
            $handler->render($req, new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too Many Requests'));
        }
        Http::assertSent(function ($request) {
            $json = $request->data();
            return ($json['endpoint'] ?? null) === 'search/suggestions'
                && ($json['threshold_percentage'] ?? 0) >= 80;
        });
    }
}
