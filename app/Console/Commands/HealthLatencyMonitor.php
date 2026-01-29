<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class HealthLatencyMonitor extends Command
{
    protected $signature = 'health:latency-monitor';
    protected $description = 'Evaluate rolling average latency and send alerts based on thresholds';

    public function handle(): int
    {
        $env = config('app.env');
        $critical = (int) (env('ALERT_LATENCY_THRESHOLD_MS') ?: ($env === 'production' ? 500 : 1000));
        $warning = (int) round($critical * 0.8);
        $windowSec = 300; // 5 minutes
        $now = time();
        $services = [
            'messaging.conversations.index',
            'messaging.conversations.show',
            'messaging.messages.index',
            'messaging.messages.store',
            'health.check',
            'health.live',
            'health.ready',
        ];
        foreach ($services as $svc) {
            $avg = $this->rollingAverage($svc, $now - $windowSec, $now);
            if ($avg <= 0) continue;
            $level = null;
            if ($avg >= $critical) $level = 'critical';
            elseif ($avg >= $warning) $level = 'warning';
            if (!$level) continue;
            if (!$this->canAlert($svc)) continue;
            if (!$this->rateLimitOk()) continue;
            $this->sendAlert($svc, $avg, $level, $critical, $warning);
            $this->markAlert($svc);
        }
        return 0;
    }

    protected function rollingAverage(string $svc, int $from, int $to): float
    {
        try {
            $key = "metrics:latency:{$svc}:samples";
            $rows = Redis::zrangebyscore($key, $from, $to, ['WITHSCORES' => true]);
            $sum = 0.0; $cnt = 0;
            foreach ($rows as $ms) { $sum += (float) $ms; $cnt++; }
            return $cnt > 0 ? round($sum / $cnt, 2) : 0.0;
        } catch (\Throwable $e) {
            $key = "metrics:latency:{$svc}:samples";
            $arr = Cache::get($key, []);
            $sum = 0.0; $cnt = 0;
            foreach ($arr as $row) {
                $ts = (int) ($row['ts'] ?? 0);
                if ($ts >= $from && $ts <= $to) {
                    $sum += (float) ($row['ms'] ?? 0);
                    $cnt++;
                }
            }
            return $cnt > 0 ? round($sum / $cnt, 2) : 0.0;
        }
    }

    protected function canAlert(string $svc): bool
    {
        $lastKey = "metrics:alert:last:{$svc}";
        $cbMinutes = (int) (env('ALERT_CIRCUIT_BREAKER_MINUTES') ?: 15);
        $last = Cache::get($lastKey);
        if ($last && (time() - (int)$last) < ($cbMinutes * 60)) return false;
        return true;
    }

    protected function markAlert(string $svc): void
    {
        Cache::put("metrics:alert:last:{$svc}", time(), 3600);
        $hourBucket = date('YmdH');
        $countKey = "metrics:alert:count:{$hourBucket}";
        $count = (int) Cache::get($countKey, 0);
        Cache::put($countKey, $count + 1, 3600);
    }

    protected function rateLimitOk(): bool
    {
        $hourBucket = date('YmdH');
        $countKey = "metrics:alert:count:{$hourBucket}";
        $count = (int) Cache::get($countKey, 0);
        $limit = (int) (env('ALERT_RATE_LIMIT_PER_HOUR') ?: 5);
        return $count < $limit;
    }

    protected function sendAlert(string $svc, float $avg, string $level, int $critical, int $warning): void
    {
        $timestamp = now()->toIso8601String();
        $dashboard = url('/admin/verification'); // placeholder dashboard, replace with actual monitoring page if exists
        $payload = [
            'service' => $svc,
            'avg_ms' => $avg,
            'threshold' => $level === 'critical' ? $critical : $warning,
            'level' => $level,
            'timestamp' => $timestamp,
            'dashboard' => $dashboard,
        ];
        $this->notifySlack($payload);
        $this->notifyEmail($payload);
        Log::warning('Latency alert', $payload);
    }

    protected function notifySlack(array $payload): void
    {
        $url = env('SLACK_WEBHOOK_URL');
        if (!$url) return;
        try {
            $text = sprintf(
                ":rotating_light: [%s] Latency %s avg=%.2fms threshold=%d at %s\nDashboard: %s",
                $payload['service'], strtoupper($payload['level']), $payload['avg_ms'], $payload['threshold'], $payload['timestamp'], $payload['dashboard']
            );
            $body = json_encode(['text' => $text]);
            $u = new \GuzzleHttp\Psr7\Uri($url);
            $client = new \GuzzleHttp\Client();
            $client->post($u, ['headers' => ['Content-Type' => 'application/json'], 'body' => $body, 'timeout' => 5]);
        } catch (\Throwable $e) {}
    }

    protected function notifyEmail(array $payload): void
    {
        $to = env('ALERT_EMAIL_TO');
        $from = env('ALERT_EMAIL_FROM');
        if (!$to || !$from) return;
        try {
            $subject = sprintf('[%s] Latency %s alert', $payload['service'], strtoupper($payload['level']));
            $text = sprintf(
                "Service: %s\nLevel: %s\nAverage: %.2f ms\nThreshold: %d ms\nTime: %s\nDashboard: %s",
                $payload['service'], $payload['level'], $payload['avg_ms'], $payload['threshold'], $payload['timestamp'], $payload['dashboard']
            );
            \Illuminate\Support\Facades\Mail::raw($text, function ($message) use ($to, $from, $subject) {
                $message->to($to)->from($from)->subject($subject);
            });
        } catch (\Throwable $e) {}
    }
}
