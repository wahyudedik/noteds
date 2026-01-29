<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    /**
     * Comprehensive health check endpoint.
     * Returns detailed status of all system components.
     */
    public function check(Request $request): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'cache' => $this->checkCache(),
            'midtrans' => $this->checkMidtrans(),
            'mediastack' => $this->checkMediaStack(),
            'disk' => $this->checkDiskSpace(),
            'memory' => $this->checkMemory(),
            'messaging_latency' => $this->checkMessagingLatency(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Liveness probe - indicates the application is running.
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Readiness probe - indicates the application is ready to serve traffic.
     */
    public function ready(): JsonResponse
    {
        $criticalChecks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $allReady = collect($criticalChecks)->every(fn($check) => $check['status'] === 'healthy');

        return response()->json([
            'status' => $allReady ? 'ready' : 'not_ready',
            'timestamp' => now()->toIso8601String(),
            'checks' => $criticalChecks,
        ], $allReady ? 200 : 503);
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $queryTime = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $queryTime) * 1000; // Convert to milliseconds

            return [
                'status' => 'healthy',
                'response_time_ms' => round($queryTime, 2),
                'connection' => config('database.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connectivity.
     */
    protected function checkRedis(): array
    {
        try {
            $startTime = microtime(true);
            Redis::connection()->ping();
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
            ];
        } catch (\Exception $e) {
            Log::warning('Redis health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue worker status.
     */
    protected function checkQueue(): array
    {
        try {
            $queueConnection = config('queue.default');
            $queueDriver = config("queue.connections.{$queueConnection}.driver");

            // For database queue, check if jobs table exists and has recent activity
            if ($queueDriver === 'database') {
                $pendingJobs = DB::table('jobs')->count();
                $failedJobs = DB::table('failed_jobs')->count();

                return [
                    'status' => 'healthy',
                    'driver' => $queueDriver,
                    'pending_jobs' => $pendingJobs,
                    'failed_jobs' => $failedJobs,
                ];
            }

            // For Redis queue, check Redis connection
            if ($queueDriver === 'redis') {
                try {
                    Redis::connection()->ping();
                    return [
                        'status' => 'healthy',
                        'driver' => $queueDriver,
                    ];
                } catch (\Exception $e) {
                    return [
                        'status' => 'unhealthy',
                        'driver' => $queueDriver,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // For sync queue (development)
            return [
                'status' => 'healthy',
                'driver' => $queueDriver,
                'note' => 'Using sync driver (development mode)',
            ];
        } catch (\Exception $e) {
            Log::warning('Queue health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity.
     */
    protected function checkCache(): array
    {
        try {
            $cacheDriver = config('cache.default');
            $testKey = 'health_check_' . time();
            $testValue = 'test';

            $startTime = microtime(true);
            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'driver' => $cacheDriver,
                    'response_time_ms' => round($responseTime, 2),
                ];
            }

            return [
                'status' => 'unhealthy',
                'driver' => $cacheDriver,
                'error' => 'Cache read/write test failed',
            ];
        } catch (\Exception $e) {
            Log::warning('Cache health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Midtrans API status.
     */
    protected function checkMidtrans(): array
    {
        try {
            $serverKey = config('midtrans.server_key');
            $isProduction = config('midtrans.is_production', false);

            if (empty($serverKey)) {
                return [
                    'status' => 'unhealthy',
                    'error' => 'Midtrans server key not configured',
                ];
            }

            // For health check, we'll just verify configuration is present
            // Actual API calls would be too expensive for frequent health checks
            return [
                'status' => 'healthy',
                'mode' => $isProduction ? 'production' : 'sandbox',
                'configured' => true,
            ];
        } catch (\Exception $e) {
            Log::warning('Midtrans health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check MediaStack API status.
     */
    protected function checkMediaStack(): array
    {
        try {
            $apiKey = config('mediastack.api_key');
            $apiEndpoint = config('mediastack.api_endpoint');

            if (empty($apiKey)) {
                return [
                    'status' => 'unhealthy',
                    'error' => 'MediaStack API key not configured',
                ];
            }

            // For health check, we'll just verify configuration is present
            // Actual API calls would consume quota
            return [
                'status' => 'healthy',
                'endpoint' => $apiEndpoint,
                'configured' => true,
            ];
        } catch (\Exception $e) {
            Log::warning('MediaStack health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check disk space.
     */
    protected function checkDiskSpace(): array
    {
        try {
            $storagePath = storage_path();
            $totalBytes = disk_total_space($storagePath);
            $freeBytes = disk_free_space($storagePath);
            $usedBytes = $totalBytes - $freeBytes;
            $usedPercentage = ($usedBytes / $totalBytes) * 100;

            $status = 'healthy';
            if ($usedPercentage > 90) {
                $status = 'critical';
            } elseif ($usedPercentage > 80) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'total_gb' => round($totalBytes / (1024 ** 3), 2),
                'free_gb' => round($freeBytes / (1024 ** 3), 2),
                'used_gb' => round($usedBytes / (1024 ** 3), 2),
                'used_percentage' => round($usedPercentage, 2),
            ];
        } catch (\Exception $e) {
            Log::warning('Disk space health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check memory usage.
     */
    protected function checkMemory(): array
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

            $usagePercentage = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

            $status = 'healthy';
            if ($usagePercentage > 90) {
                $status = 'critical';
            } elseif ($usagePercentage > 80) {
                $status = 'warning';
            }

            return [
                'status' => $status,
                'current_mb' => round($memoryUsage / (1024 ** 2), 2),
                'peak_mb' => round($memoryPeak / (1024 ** 2), 2),
                'limit_mb' => $memoryLimit > 0 ? round($memoryLimit / (1024 ** 2), 2) : 'unlimited',
                'usage_percentage' => $memoryLimit > 0 ? round($usagePercentage, 2) : 0,
            ];
        } catch (\Exception $e) {
            Log::warning('Memory health check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkMessagingLatency(): array
    {
        try {
            $keys = [
                'conversations.index',
                'conversations.show',
                'messages.index',
                'messages.store',
            ];
            $result = [];
            foreach ($keys as $k) {
                $sum = null;
                $cnt = null;
                try {
                    $sum = \Illuminate\Support\Facades\Redis::get("metrics:messaging:{$k}:sum_ms");
                    $cnt = \Illuminate\Support\Facades\Redis::get("metrics:messaging:{$k}:count");
                } catch (\Throwable $e) {
                    $sum = \Illuminate\Support\Facades\Cache::get("metrics:messaging:{$k}:sum_ms", 0);
                    $cnt = \Illuminate\Support\Facades\Cache::get("metrics:messaging:{$k}:count", 0);
                }
                $sum = (float) ($sum ?: 0);
                $cnt = (int) ($cnt ?: 0);
                $avg = $cnt > 0 ? round($sum / $cnt, 2) : 0.0;
                $result[$k] = ['avg_ms' => $avg, 'count' => $cnt];
            }
            return [
                'status' => 'healthy',
                'endpoints' => $result,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Messaging latency check failed: ' . $e->getMessage());
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function alertConfig(): array
    {
        $env = config('app.env');
        $critical = (int) (env('ALERT_LATENCY_THRESHOLD_MS') ?: ($env === 'production' ? 500 : 1000));
        $warning = (int) round($critical * 0.8);
        $cbMinutes = (int) (env('ALERT_CIRCUIT_BREAKER_MINUTES') ?: 15);
        $rateHour = (int) (env('ALERT_RATE_LIMIT_PER_HOUR') ?: 5);
        return [
            'env' => $env,
            'critical_ms' => $critical,
            'warning_ms' => $warning,
            'circuit_breaker_minutes' => $cbMinutes,
            'rate_limit_per_hour' => $rateHour,
            'slack_webhook' => !!env('SLACK_WEBHOOK_URL'),
            'email_to' => env('ALERT_EMAIL_TO'),
            'email_from' => env('ALERT_EMAIL_FROM'),
            'window_minutes' => 5,
            'evaluation_interval_seconds' => 30,
        ];
    }

    /**
     * Parse memory limit string to bytes.
     */
    protected function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return 0; // Unlimited
        }

        $limit = trim($limit);
        if (empty($limit)) {
            return 0;
        }

        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
