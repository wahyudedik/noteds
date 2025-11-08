<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostModerationController extends Controller
{
    /**
     * Display moderated posts and reports.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status'); // visible|hidden
        $reportStatus = $request->input('report_status'); // pending|reviewed|resolved|dismissed|unreported

        $postsQuery = Post::with(['user'])
            ->withCount([
                'reports as pending_reports_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->withCount('reports')
            ->orderByDesc('pending_reports_count')
            ->orderByDesc('created_at');

        if ($search) {
            $postsQuery->where(function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'visible') {
            $postsQuery->where('is_hidden', false);
        } elseif ($status === 'hidden') {
            $postsQuery->where('is_hidden', true);
        }

        if ($reportStatus) {
            if ($reportStatus === 'unreported') {
                $postsQuery->doesntHave('reports');
            } else {
                $postsQuery->whereHas('reports', function ($query) use ($reportStatus) {
                    $query->where('status', $reportStatus);
                });
            }
        }

        $posts = $postsQuery->paginate(20)->withQueryString();

        return view('admin.forum.moderation.index', compact('posts', 'search', 'status', 'reportStatus'));
    }

    /**
     * Show post details and associated reports.
     */
    public function show(Post $post): View
    {
        $post->load(['user', 'note']);

        $reports = $post->reports()
            ->with(['user', 'reviewer'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.forum.moderation.show', compact('post', 'reports'));
    }

    /**
     * Hide a post from the forum.
     */
    public function hide(Post $post): RedirectResponse
    {
        $post->update([
            'is_hidden' => true,
            'hidden_at' => now(),
        ]);

        return back()->with('success', 'Post has been hidden successfully.');
    }

    /**
     * Unhide a previously hidden post.
     */
    public function unhide(Post $post): RedirectResponse
    {
        $post->update([
            'is_hidden' => false,
            'hidden_at' => null,
        ]);

        return back()->with('success', 'Post is now visible to users.');
    }

    /**
     * Permanently delete a post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.forum.moderation.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Update the status of a specific report.
     */
    public function updateReportStatus(Request $request, PostReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report status updated successfully.');
    }
}
