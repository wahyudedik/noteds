<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class AnalyticsUsageReport extends Command
{
    protected $signature = 'analytics:usage-report {--days=90}';
    protected $description = 'Generate usage report for Analytics dashboard engagement';

    public function handle(): int
    {
        $days = (int)($this->option('days') ?? 90);
        $since = Carbon::now()->subDays($days);
        $totalEvents = DB::table('analytics_events')->count();
        $periodEvents = DB::table('analytics_events')->where('created_at', '>=', $since)->count();
        $byType = DB::table('analytics_events')
            ->select(DB::raw('type, COUNT(*) as c'))
            ->where('created_at', '>=', $since)
            ->groupBy('type')->get()->mapWithKeys(fn($r) => [$r->type => (int)$r->c])->toArray();
        $dashboardAccess = $byType['analytics_dashboard_access'] ?? 0;
        $errorEvents = DB::table('analytics_events')
            ->where('created_at', '>=', $since)
            ->where(function ($q) {
                $q->where('type', 'like', '%error%')
                    ->orWhereRaw("JSON_EXTRACT(payload, '$.status') = 'error'")
                    ->orWhereRaw("JSON_EXTRACT(payload, '$.code') IN ('server_error','too_many_requests')");
            })->count();
        $report = [
            'generated_at' => now()->toIso8601String(),
            'window_days' => $days,
            'totals' => [
                'all_time' => $totalEvents,
                'period' => $periodEvents,
            ],
            'dashboard_access' => $dashboardAccess,
            'error_events' => $errorEvents,
            'by_type' => $byType,
        ];
        $path = storage_path('logs/analytics_usage_report.json');
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT));
        $this->info("Report written to {$path}");
        return 0;
    }
}
