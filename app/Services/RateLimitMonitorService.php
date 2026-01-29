<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RateLimitMonitorService
{
    public function triggerAlert(string $endpoint, int $endpointCount, int $aggregateCount): void
    {
        $key = 'rl:alert:last:' . $endpoint;
        $last = Cache::get($key);
        if ($last && now()->diffInSeconds($last) < 30) {
            return;
        }
        Cache::put($key, now(), 60);
        $payload = [
            'severity' => 'high',
            'endpoint' => $endpoint,
            'endpoint_count_minute' => $endpointCount,
            'aggregate_count_minute' => $aggregateCount,
            'ts' => now()->toIso8601String(),
        ];
        $webhook = config('ratelimit.alert_thresholds.webhook');
        if ($webhook) {
            $token = env('RL_ALERT_TOKEN');
            $attempts = 0;
            $delay = 100;
            while ($attempts < 3) {
                $attempts++;
                try {
                    $resp = Http::withHeaders($token ? ['Authorization' => "Bearer $token"] : [])
                        ->timeout(5)
                        ->post($webhook, $payload);
                    if ($resp->successful()) {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning('rl_alert_webhook_failed', ['message' => $e->getMessage(), 'attempt' => $attempts]);
                }
                usleep($delay * 1000);
                $delay *= 2;
            }
        }
        $email = config('ratelimit.alert_thresholds.email');
        if ($email) {
            try {
                Log::channel('rate_limit')->warning('rl_alert_email', ['to' => $email] + $payload);
            } catch (\Throwable $e) {
            }
        }
    }

    public function checkUsageThreshold(string $endpoint, int $endpointCount): void
    {
        $threshold = (int) (config('ratelimit.alert_thresholds.per_endpoint_per_minute') ?? 50);
        $percent = $threshold > 0 ? ($endpointCount / $threshold) : 0;
        if ($percent >= 0.8) {
            $payload = [
                'severity' => 'medium',
                'endpoint' => $endpoint,
                'current_usage' => $endpointCount,
                'threshold_percentage' => round($percent * 100, 2),
                'ts' => now()->toIso8601String(),
            ];
            $webhook = config('ratelimit.alert_thresholds.webhook');
            if ($webhook) {
                $token = env('RL_ALERT_TOKEN');
                try {
                    Http::withHeaders($token ? ['Authorization' => "Bearer $token"] : [])
                        ->timeout(5)
                        ->post($webhook, $payload);
                } catch (\Throwable $e) {
                    Log::warning('rl_usage_alert_webhook_failed', ['message' => $e->getMessage()]);
                }
            }
        }
    }

    public function getMetrics(): array
    {
        $bucket = now()->format('YmdHi');
        return [
            'search_suggestions' => (int) Cache::get("rl:429:endpoint:search/suggestions:$bucket", 0),
            'streams_chat_store' => (int) Cache::get("rl:429:endpoint:api/streams/*/chat:$bucket", 0),
            'analytics_events_store' => (int) Cache::get("rl:429:endpoint:api/analytics/events:$bucket", 0),
            'aggregate' => (int) Cache::get("rl:429:aggregate:$bucket", 0),
        ];
    }

    public function getSeries(string $endpoint, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return \App\Models\RateLimitStat::query()
            ->where('endpoint', $endpoint)
            ->whereBetween('minute_bucket', [$from, $to])
            ->orderBy('minute_bucket')
            ->get(['minute_bucket', 'count'])
            ->map(fn ($r) => ['t' => $r->minute_bucket, 'v' => (int) $r->count])
            ->all();
    }
}
