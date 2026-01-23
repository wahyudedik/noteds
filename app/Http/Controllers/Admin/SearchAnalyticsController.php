<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SearchAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'type' => 'nullable|in:all,posts,users,products,articles',
            'segment' => 'nullable|in:all,user,admin,brand,clipper',
            'period' => 'nullable|in:daily,weekly,monthly',
        ]);

        $dateFrom = $request->input('date_from', now()->subMonth()->startOfDay());
        $dateTo = $request->input('date_to', now()->endOfDay());
        $type = $request->input('type', 'all');
        $segment = $request->input('segment', 'all');
        $period = $request->input('period', 'daily');

        $baseQuery = SearchHistory::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($type !== 'all') {
            $baseQuery->whereJsonContains('filters->type', $type);
        }

        if ($segment !== 'all') {
            $baseQuery->whereHas('user', function ($q) use ($segment) {
                switch ($segment) {
                    case 'admin':
                        $q->where('role', 'admin');
                        break;
                    case 'user':
                        $q->where('role', 'user')->whereNull('clipper_role');
                        break;
                    case 'brand':
                        $q->where('clipper_role', 'brand');
                        break;
                    case 'clipper':
                        $q->where('clipper_role', 'clipper');
                        break;
                }
            });
        }

        $totalSearches = (clone $baseQuery)->count();
        $zeroResultCount = (clone $baseQuery)->where('zero_result', true)->count();
        $avgDuration = (clone $baseQuery)->avg('duration_ms') ?? 0;
        $successRate = $totalSearches > 0 ? round((($totalSearches - $zeroResultCount) / $totalSearches) * 100, 2) : 0;

        // Top queries
        $topQueries = (clone $baseQuery)
            ->select('query', DB::raw('COUNT(*) as cnt'))
            ->groupBy('query')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['query' => $r->query, 'count' => (int) $r->cnt]);

        // Timeline aggregation
        $groupFormat = match ($period) {
            'weekly' => '%Y-%W',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $timelineAll = (clone $baseQuery)
            ->select(DB::raw("strftime('$groupFormat', created_at) as bucket"), DB::raw('COUNT(*) as cnt'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn($r) => ['bucket' => $r->bucket, 'count' => (int) $r->cnt]);

        $timelineZero = (clone $baseQuery)
            ->where('zero_result', true)
            ->select(DB::raw("strftime('$groupFormat', created_at) as bucket"), DB::raw('COUNT(*) as cnt'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(fn($r) => ['bucket' => $r->bucket, 'count' => (int) $r->cnt]);

        return Inertia::render('Admin/SearchAnalytics', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'type' => $type,
                'segment' => $segment,
                'period' => $period,
            ],
            'metrics' => [
                'total' => $totalSearches,
                'zero_result' => $zeroResultCount,
                'avg_duration_ms' => (int) round($avgDuration),
                'success_rate' => $successRate,
            ],
            'topQueries' => $topQueries,
            'timelineAll' => $timelineAll,
            'timelineZero' => $timelineZero,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->input('date_from', now()->subMonth()->startOfDay());
        $dateTo = $request->input('date_to', now()->endOfDay());

        $rows = SearchHistory::whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->limit(5000)
            ->get(['user_id','query','filters','zero_result','duration_ms','created_at']);

        if ($request->input('format') === 'csv') {
            $filename = 'search_analytics_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['user_id','query','filters_json','zero_result','duration_ms','created_at']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->user_id,
                        $r->query,
                        json_encode($r->filters),
                        $r->zero_result ? 1 : 0,
                        $r->duration_ms,
                        $r->created_at,
                    ]);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

        $html = view('admin.search_analytics_pdf', [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ])->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        return $pdf->download('search_analytics_' . now()->format('Ymd_His') . '.pdf');
    }
}
