<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostAnalyticsController extends Controller
{
    public function __construct(
        private PostAnalyticsService $analyticsService
    ) {}

    /**
     * Display analytics for a post.
     */
    public function show(Request $request, Post $post): InertiaResponse
    {
        // Only post owner can view analytics
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $days = (int) $request->input('days', 30);
        $analytics = $this->analyticsService->getAnalytics($post, $days);

        return Inertia::render('Posts/Analytics', [
            'post' => $post,
            'analytics' => $analytics,
        ]);
    }

    /**
     * Export analytics as CSV.
     */
    public function export(Request $request, Post $post): StreamedResponse
    {
        // Only post owner can export analytics
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $days = (int) $request->input('days', 30);
        $csvData = $this->analyticsService->exportCsv($post, $days);

        $filename = 'post-analytics-' . $post->id . '-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}


