<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof ThrottleRequestsException) {
            $this->logRateLimit($request);
        }
        return parent::render($request, $e);
    }

    private function logRateLimit(Request $request): void
    {
        $path = $request->path();
        $ip = $request->ip();
        $userId = optional($request->user())->id;
        $ua = $request->userAgent();
        $nowBucket = now()->format('YmdHi');
        $bucketDt = now()->startOfMinute();
        $stat = \App\Models\RateLimitStat::query()->where([
            'endpoint' => $path,
            'minute_bucket' => $bucketDt,
        ])->first();
        if ($stat) {
            $stat->increment('count');
        } else {
            \App\Models\RateLimitStat::create([
                'endpoint' => $path,
                'minute_bucket' => $bucketDt,
                'count' => 1,
            ]);
        }
        $keyEndpoint = "rl:429:endpoint:$path:$nowBucket";
        $keyAggregate = "rl:429:aggregate:$nowBucket";
        $keyIp = "rl:429:ip:$ip:$nowBucket";
        $endpointCount = (int) Cache::increment($keyEndpoint);
        $aggregateCount = (int) Cache::increment($keyAggregate);
        $ipCount = (int) Cache::increment($keyIp);
        Cache::put($keyEndpoint, $endpointCount, 120);
        Cache::put($keyAggregate, $aggregateCount, 120);
        Cache::put($keyIp, $ipCount, 120);
        $severity = 'normal';
        $thresholds = config('ratelimit.alert_thresholds');
        if ($endpointCount > ($thresholds['per_endpoint_per_minute'] ?? 50) || $aggregateCount > ($thresholds['aggregate_per_minute'] ?? 100)) {
            $severity = 'spike';
            app(\App\Services\RateLimitMonitorService::class)->triggerAlert($path, $endpointCount, $aggregateCount);
        }
        app(\App\Services\RateLimitMonitorService::class)->checkUsageThreshold($path, $endpointCount);
        Log::channel('rate_limit')->warning('429', [
            'ts' => now()->toIso8601String(),
            'ip' => $ip,
            'ua' => $ua,
            'user_id' => $userId,
            'endpoint' => $path,
            'endpoint_count_minute' => $endpointCount,
            'aggregate_count_minute' => $aggregateCount,
            'severity' => $severity,
        ]);
    }
}
