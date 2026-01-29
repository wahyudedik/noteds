<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RateLimitLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_suggestions_429_is_logged()
    {
        @unlink(storage_path('logs/rate_limit.log'));
        $request = \Illuminate\Http\Request::create('/search/suggestions', 'GET', ['q' => 'a']);
        $request->setLaravelSession(app('session.store'));
        app(\App\Exceptions\Handler::class)->render($request, new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too Many Requests'));
        $bucket = now()->format('YmdHi');
        $endpointCount = (int) \Illuminate\Support\Facades\Cache::get("rl:429:endpoint:search/suggestions:$bucket", 0);
        $this->assertTrue($endpointCount > 0);
    }
}
