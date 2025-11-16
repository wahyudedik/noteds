<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiMonitoringService
{
    /**
     * Track AI request performance.
     */
    public function trackRequest(string $type, float $duration, bool $success, ?int $userId = null, ?string $error = null): void
    {
        try {
            $date = now()->format('Y-m-d');
            $hour = now()->format('H');

            // Cache key for this hour
            $cacheKey = "ai_monitoring:{$type}:{$date}:{$hour}";

            $stats = Cache::get($cacheKey, [
                'count' => 0,
                'total_duration' => 0,
                'success_count' => 0,
                'error_count' => 0,
                'avg_duration' => 0,
            ]);

            $stats['count']++;
            $stats['total_duration'] += $duration;
            $stats['avg_duration'] = $stats['total_duration'] / $stats['count'];

            if ($success) {
                $stats['success_count']++;
            } else {
                $stats['error_count']++;
            }

            // Store in cache (1 hour TTL)
            Cache::put($cacheKey, $stats, now()->addHour());

            // Log to database (optional, for long-term storage)
            $this->logToDatabase($type, $duration, $success, $userId, $error);
        } catch (\Exception $e) {
            Log::error('AI monitoring tracking failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get performance statistics.
     */
    public function getStats(string $type = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(7);
        $endDate = $endDate ?? now();

        $query = DB::table('ai_request_logs')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        $stats = $query->selectRaw('
                type,
                COUNT(*) as total_requests,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as error_count,
                AVG(duration) as avg_duration,
                MIN(duration) as min_duration,
                MAX(duration) as max_duration,
                PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY duration) as p95_duration
            ')
            ->groupBy('type')
            ->get();

        return $stats->toArray();
    }

    /**
     * Get real-time performance metrics.
     */
    public function getRealtimeMetrics(): array
    {
        $now = now();
        $lastHour = $now->copy()->subHour();

        $metrics = [];

        // Get metrics for each AI type
        $types = ['summary', 'tags', 'qa', 'embedding', 'topic_detection', 'context_linking'];

        foreach ($types as $type) {
            $cacheKey = "ai_monitoring:{$type}:{$now->format('Y-m-d')}:{$now->format('H')}";
            $currentStats = Cache::get($cacheKey, [
                'count' => 0,
                'avg_duration' => 0,
                'success_count' => 0,
                'error_count' => 0,
            ]);

            $metrics[$type] = $currentStats;
        }

        return $metrics;
    }

    /**
     * Log to database for long-term storage.
     */
    protected function logToDatabase(string $type, float $duration, bool $success, ?int $userId = null, ?string $error = null): void
    {
        try {
            // Only log every 10th request to avoid database overload
            if (rand(1, 10) !== 1) {
                return;
            }

            DB::table('ai_request_logs')->insert([
                'type' => $type,
                'user_id' => $userId,
                'duration' => $duration,
                'success' => $success,
                'error' => $error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - monitoring shouldn't break the app
            Log::warning('AI monitoring database log failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get health status of AI services.
     */
    public function getHealthStatus(): array
    {
        $lastHour = now()->subHour();
        $recentRequests = DB::table('ai_request_logs')
            ->where('created_at', '>=', $lastHour)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success,
                AVG(duration) as avg_duration
            ')
            ->first();

        $successRate = $recentRequests->total > 0 
            ? ($recentRequests->success / $recentRequests->total) * 100 
            : 100;

        return [
            'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
            'success_rate' => round($successRate, 2),
            'avg_duration' => round($recentRequests->avg_duration ?? 0, 2),
            'total_requests_last_hour' => $recentRequests->total ?? 0,
        ];
    }
}

