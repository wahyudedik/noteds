<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(): View
    {
        $health = [
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'cache' => $this->checkCache(),
            'scheduler' => $this->checkScheduler(),
            'broadcaster' => $this->checkBroadcaster(),
        ];

        // Check for critical alerts
        $alerts = $this->checkAlerts($health);

        return view('admin.system-health.index', compact('health', 'alerts'));
    }

    public function testBroadcaster(Request $request)
    {
        try {
            $driver = config('broadcasting.default');
            $config = config("broadcasting.connections.{$driver}");

            if ($driver === 'pusher') {
                $pusher = new \Pusher\Pusher(
                    $config['key'],
                    $config['secret'],
                    $config['app_id'],
                    $config['options'] ?? []
                );
                $pusher->trigger('test-channel', 'test-event', ['message' => 'Test from admin panel']);
                return back()->with('success', 'Broadcaster test successful! Check your Pusher dashboard.');
            } elseif ($driver === 'ably') {
                // Ably test would require SDK
                return back()->with('info', 'Ably broadcaster detected. Manual test recommended via Ably dashboard.');
            } elseif ($driver === 'log') {
                return back()->with('warning', 'Broadcaster is set to "log" - no real-time broadcasting. Switch to Pusher/Ably for production.');
            } else {
                return back()->with('info', "Broadcaster driver: {$driver}. Manual testing recommended.");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Broadcaster test failed: ' . $e->getMessage());
        }
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $driver = config('database.default');
            $status = 'healthy';
            $message = 'Database connection successful';
            $details = [
                'driver' => $driver,
                'host' => config("database.connections.{$driver}.host") ?? 'N/A',
            ];
        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Database connection failed: ' . $e->getMessage();
            $details = [];
        }

        return compact('status', 'message', 'details');
    }

    private function checkQueue(): array
    {
        $driver = config('queue.default');
        $status = 'healthy';
        $message = "Queue driver: {$driver}";
        $details = ['driver' => $driver];

        if ($driver === 'redis') {
            try {
                Redis::connection()->ping();
                $details['connection'] = 'OK';
            } catch (\Exception $e) {
                $status = 'error';
                $message = 'Redis queue connection failed: ' . $e->getMessage();
                $details['connection'] = 'FAILED';
            }
        } elseif ($driver === 'database') {
            try {
                DB::table('jobs')->count();
                $details['jobs_table'] = 'OK';
            } catch (\Exception $e) {
                $status = 'error';
                $message = 'Database queue table missing: ' . $e->getMessage();
                $details['jobs_table'] = 'FAILED';
            }
        }

        // Check if queue worker is running (heuristic: recent job processing)
        try {
            $recentJob = DB::table('jobs')->where('created_at', '>', now()->subMinutes(5))->exists();
            $details['worker_active'] = $recentJob ? 'Possibly active' : 'Unknown';
        } catch (\Exception $e) {
            $details['worker_active'] = 'N/A';
        }

        // Monitor pending and failed jobs
        try {
            $pendingCount = DB::table('jobs')->count();
            $failedCount = DB::table('failed_jobs')->count();
            $details['pending_jobs'] = $pendingCount;
            $details['failed_jobs'] = $failedCount;

            if ($failedCount > 0) {
                $status = 'warning';
                $message .= " ({$failedCount} failed jobs)";
            }
            if ($pendingCount > 100) {
                $status = 'warning';
                $message .= " ({$pendingCount} pending - high queue)";
            }
        } catch (\Exception $e) {
            $details['pending_jobs'] = 'N/A';
            $details['failed_jobs'] = 'N/A';
        }

        return compact('status', 'message', 'details');
    }

    private function checkCache(): array
    {
        $driver = config('cache.default');
        $status = 'healthy';
        $message = "Cache driver: {$driver}";
        $details = ['driver' => $driver];

        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            if ($value === 'ok') {
                $details['test'] = 'OK';
            } else {
                $status = 'warning';
                $message = 'Cache test failed - value mismatch';
                $details['test'] = 'FAILED';
            }
        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Cache test failed: ' . $e->getMessage();
            $details['test'] = 'FAILED';
        }

        return compact('status', 'message', 'details');
    }

    private function checkScheduler(): array
    {
        $status = 'healthy';
        $message = 'Scheduler configured';
        $details = [];

        // Check if schedule:run is in cron (heuristic: check last run marker)
        $lastRunMarker = Cache::get('scheduler_last_run');
        if ($lastRunMarker) {
            $details['last_run'] = 'Detected at ' . $lastRunMarker;
            $details['status'] = 'Likely running';
        } else {
            $details['last_run'] = 'Not detected';
            $details['status'] = 'Ensure cron is set: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1';
        }

        // List scheduled commands
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = $schedule->events();
        $details['scheduled_commands'] = count($events) . ' commands scheduled';

        return compact('status', 'message', 'details');
    }

    private function checkBroadcaster(): array
    {
        $driver = config('broadcasting.default');
        $status = 'healthy';
        $message = "Broadcaster driver: {$driver}";
        $details = ['driver' => $driver];

        $config = config("broadcasting.connections.{$driver}");

        if ($driver === 'pusher') {
            $details['app_id'] = $config['app_id'] ?? 'Not set';
            $details['key'] = $config['key'] ? (substr($config['key'], 0, 8) . '...') : 'Not set';
            $details['secret'] = $config['secret'] ? 'Set' : 'Not set';
            $details['cluster'] = $config['options']['cluster'] ?? 'Not set';
            if (empty($config['key']) || empty($config['secret']) || empty($config['app_id'])) {
                $status = 'error';
                $message = 'Pusher configuration incomplete';
            }
        } elseif ($driver === 'ably') {
            $details['key'] = $config['key'] ? (substr($config['key'], 0, 8) . '...') : 'Not set';
            if (empty($config['key'])) {
                $status = 'error';
                $message = 'Ably configuration incomplete';
            }
        } elseif ($driver === 'log') {
            $status = 'warning';
            $message = 'Broadcaster is set to "log" - no real-time broadcasting';
            $details['note'] = 'Switch to Pusher/Ably for production real-time features';
        } else {
            $details['note'] = 'Manual configuration check recommended';
        }

        // Check if Echo is configured in frontend (heuristic: check if JS config exists)
        $details['frontend_note'] = 'Ensure Echo is initialized in resources/js/app.js or layout';

        return compact('status', 'message', 'details');
    }

    private function checkAlerts(array $health): array
    {
        $alerts = [];
        $criticalComponents = ['database', 'queue'];

        foreach ($criticalComponents as $component) {
            if (isset($health[$component]) && $health[$component]['status'] === 'error') {
                $alerts[] = [
                    'type' => 'critical',
                    'component' => ucfirst($component),
                    'message' => $health[$component]['message'],
                ];
            }
        }

        // Check scheduler (warning if not detected)
        if (isset($health['scheduler']) && !Cache::has('scheduler_last_run')) {
            $lastCheck = Cache::get('scheduler_last_check', 0);
            // Only alert if scheduler hasn't run in last 10 minutes
            if (now()->timestamp - $lastCheck > 600) {
                $alerts[] = [
                    'type' => 'warning',
                    'component' => 'Scheduler',
                    'message' => 'Scheduler may not be running. Check cron configuration.',
                ];
                Cache::put('scheduler_last_check', now()->timestamp, 600);
            }
        }

        // Check failed jobs
        try {
            $failedCount = DB::table('failed_jobs')->count();
            if ($failedCount > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'component' => 'Queue',
                    'message' => "{$failedCount} failed job(s) detected. Review failed_jobs table.",
                ];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Send notifications for critical alerts (only once per hour to avoid spam)
        if (!empty($alerts)) {
            $this->sendAlertsIfNeeded($alerts);
        }

        return $alerts;
    }

    private function sendAlertsIfNeeded(array $alerts): void
    {
        $criticalAlerts = array_filter($alerts, fn($a) => $a['type'] === 'critical');
        if (empty($criticalAlerts)) {
            return;
        }

        $alertKey = 'system_health_alert_' . md5(json_encode($criticalAlerts));
        $lastSent = Cache::get($alertKey);

        // Only send alert once per hour
        if (!$lastSent || (now()->timestamp - $lastSent) > 3600) {
            try {
                $admins = \App\Models\User::role('admin')->get();
                foreach ($admins as $admin) {
                    \App\Models\AppNotification::create([
                        'user_id' => $admin->id,
                        'type' => 'system_alert',
                        'title' => 'System Health Alert',
                        'message' => 'Critical component(s) down: ' . implode(', ', array_column($criticalAlerts, 'component')),
                        'link' => route('admin.system-health.index'),
                        'is_read' => false,
                        'data' => ['alerts' => $criticalAlerts],
                    ]);
                }
                Cache::put($alertKey, now()->timestamp, 3600);
            } catch (\Exception $e) {
                // Fail silently to avoid breaking health check
            }
        }
    }
}

