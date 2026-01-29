<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RateLimitDashboardController extends Controller
{
    public function metrics(Request $request)
    {
        $range = $request->input('range', '1h');
        $to = now();
        $from = match ($range) {
            '24h' => now()->subHours(24),
            '7d' => now()->subDays(7),
            default => now()->subHour(),
        };
        $svc = app(\App\Services\RateLimitMonitorService::class);
        return response()->json([
            'series' => [
                'search.suggestions' => $svc->getSeries('search/suggestions', $from, $to),
                'streams.chat.store' => $svc->getSeries('api/streams/*/chat', $from, $to),
                'analytics.events.store' => $svc->getSeries('api/analytics/events', $from, $to),
            ],
            'stats' => [
                'total_blocked' => \App\Models\RateLimitStat::whereBetween('minute_bucket', [$from, $to])->sum('count'),
                'peak' => \App\Models\RateLimitStat::whereBetween('minute_bucket', [$from, $to])->orderByDesc('count')->first(),
            ],
        ]);
    }
}
