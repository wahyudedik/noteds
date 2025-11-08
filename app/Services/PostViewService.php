<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PostViewService
{
    /**
     * Record a post view for analytics.
     */
    public function record(Post $post, ?User $viewer, Request $request): void
    {
        $viewerHash = $this->generateViewerHash($viewer, $request);
        $today = Carbon::today();

        $exists = PostView::where('post_id', $post->id)
            ->where('viewer_hash', $viewerHash)
            ->whereDate('viewed_date', $today)
            ->exists();

        if ($exists) {
            return;
        }

        PostView::create([
            'post_id' => $post->id,
            'user_id' => $viewer?->id,
            'viewer_hash' => $viewerHash,
            'viewed_date' => $today,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->header('User-Agent'), 0, 1024),
            'viewed_at' => now(),
        ]);

        $post->increment('views_count');
    }

    protected function generateViewerHash(?User $viewer, Request $request): string
    {
        if ($viewer) {
            return hash('sha256', 'user-'.$viewer->getAuthIdentifier());
        }

        $fingerprint = sprintf(
            'guest-%s-%s',
            $request->ip(),
            substr((string) $request->header('User-Agent'), 0, 255)
        );

        return hash('sha256', $fingerprint);
    }
}


