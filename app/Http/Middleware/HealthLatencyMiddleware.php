<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthLatencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $t0 = microtime(true);
        $response = $next($request);
        $elapsed = (microtime(true) - $t0) * 1000;
        $name = $request->route()?->getName() ?? 'health.unknown';
        $key = "metrics:latency:{$name}:sum_ms";
        $cntKey = "metrics:latency:{$name}:count";
        try {
            \Illuminate\Support\Facades\Redis::incrbyfloat($key, $elapsed);
            \Illuminate\Support\Facades\Redis::incr($cntKey);
            $samplesKey = "metrics:latency:{$name}:samples";
            $ts = (int) floor(microtime(true));
            \Illuminate\Support\Facades\Redis::zadd($samplesKey, [$ts => $elapsed]);
            \Illuminate\Support\Facades\Redis::zremrangebyscore($samplesKey, 0, $ts - 3600);
        } catch (\Throwable $e) {
            $sum = (float) \Illuminate\Support\Facades\Cache::get($key, 0);
            $cnt = (int) \Illuminate\Support\Facades\Cache::get($cntKey, 0);
            \Illuminate\Support\Facades\Cache::put($key, $sum + $elapsed, 600);
            \Illuminate\Support\Facades\Cache::put($cntKey, $cnt + 1, 600);
            $samplesKey = "metrics:latency:{$name}:samples";
            $arr = \Illuminate\Support\Facades\Cache::get($samplesKey, []);
            $arr[] = ['ts' => time(), 'ms' => $elapsed];
            $arr = array_filter($arr, fn ($x) => ($x['ts'] ?? 0) >= time() - 3600);
            \Illuminate\Support\Facades\Cache::put($samplesKey, $arr, 600);
        }
        return $response;
    }
}
