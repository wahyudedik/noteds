<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\RepostAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepostAnalyticsController extends Controller
{
    public function __construct(
        private RepostAnalyticsService $analyticsService
    ) {}

    /**
     * Show repost analytics untuk post.
     */
    public function show(Request $request, Post $post): Response|JsonResponse
    {
        $this->authorize('viewAnalytics', $post);

        $breakdown = $this->analyticsService->getRepostBreakdown($post);
        $engagement = $this->analyticsService->getRepostEngagement($post);
        $timeline = $this->analyticsService->getRepostTimeline($post);

        if ($request->wantsJson()) {
            return response()->json([
                'breakdown' => $breakdown,
                'engagement' => $engagement,
                'timeline' => $timeline,
            ]);
        }

        return Inertia::render('Analytics/RepostAnalytics', [
            'post' => $post,
            'breakdown' => $breakdown,
            'engagement' => $engagement,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Get repost breakdown.
     */
    public function breakdown(Request $request, Post $post): JsonResponse
    {
        $this->authorize('viewAnalytics', $post);

        $breakdown = $this->analyticsService->getRepostBreakdown($post);

        return response()->json($breakdown);
    }

    /**
     * Get repost timeline.
     */
    public function timeline(Request $request, Post $post): JsonResponse
    {
        $this->authorize('viewAnalytics', $post);

        $startDate = $request->query('start_date') 
            ? \Carbon\Carbon::parse($request->query('start_date'))
            : null;
        $endDate = $request->query('end_date')
            ? \Carbon\Carbon::parse($request->query('end_date'))
            : null;

        $timeline = $this->analyticsService->getRepostTimeline($post, $startDate, $endDate);

        return response()->json($timeline);
    }

    /**
     * Get list of reposters.
     */
    public function reposters(Request $request, Post $post): JsonResponse
    {
        $this->authorize('viewAnalytics', $post);

        $type = $request->query('type'); // 'quote', 'with_comment', 'regular', or null for all
        $limit = (int) $request->query('limit', 50);

        $reposters = $this->analyticsService->getRepostersList($post, $type, $limit);

        return response()->json($reposters);
    }

    /**
     * Get engagement metrics.
     */
    public function engagement(Request $request, Post $post): JsonResponse
    {
        $this->authorize('viewAnalytics', $post);

        $engagement = $this->analyticsService->getRepostEngagement($post);

        return response()->json($engagement);
    }

    /**
     * Export analytics data.
     */
    public function export(Request $request, Post $post)
    {
        $this->authorize('viewAnalytics', $post);

        $breakdown = $this->analyticsService->getRepostBreakdown($post);
        $engagement = $this->analyticsService->getRepostEngagement($post);
        $timeline = $this->analyticsService->getRepostTimeline($post);
        $reposters = $this->analyticsService->getRepostersList($post, null, 1000);

        $data = [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'created_at' => $post->created_at,
            ],
            'breakdown' => $breakdown,
            'engagement' => $engagement,
            'timeline' => $timeline,
            'reposters' => $reposters,
            'exported_at' => now(),
        ];

        if ($request->query('format') === 'csv') {
            return $this->exportToCsv($data);
        }

        return response()->json($data);
    }

    /**
     * Export to CSV.
     */
    private function exportToCsv(array $data)
    {
        $filename = 'repost_analytics_' . $data['post']['id'] . '_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write breakdown
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Reposts', $data['breakdown']['total']]);
            fputcsv($file, ['Regular Reposts', $data['breakdown']['regular']]);
            fputcsv($file, ['Quote Reposts', $data['breakdown']['quote']]);
            fputcsv($file, ['Reposts with Comments', $data['breakdown']['with_comments']]);
            fputcsv($file, []);
            
            // Write engagement
            fputcsv($file, ['Engagement Metric', 'Value']);
            foreach ($data['engagement'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($file, []);
            
            // Write timeline
            fputcsv($file, ['Date', 'Reposts', 'Quote Reposts', 'With Comments', 'Unique Reposters']);
            foreach ($data['timeline'] as $entry) {
                fputcsv($file, [
                    $entry->date,
                    $entry->reposts_count,
                    $entry->quote_reposts_count,
                    $entry->reposts_with_comments_count,
                    $entry->unique_reposters_count,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
