<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\ModerationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPostController extends Controller
{
    public function __construct(
        private ModerationService $moderationService
    ) {}

    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'media']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by purpose_type
        if ($request->has('purpose_type') && $request->purpose_type && $request->purpose_type !== 'all') {
            $query->where('purpose_type', $request->purpose_type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $posts = $query->latest()->paginate(20);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['status', 'purpose_type', 'search']),
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        $post->load(['user', 'media', 'comments.user', 'campaign']);

        // Get moderation history
        $moderationHistory = \App\Models\ModerationLog::where('post_id', $post->id)
            ->with(['user', 'moderator'])
            ->latest()
            ->get();

        // Get reports for this post
        $reports = \App\Models\ContentReport::where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->with(['user', 'admin'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Posts/Show', [
            'post' => $post,
            'moderation_history' => $moderationHistory,
            'reports' => $reports,
        ]);
    }

    /**
     * Moderate a post.
     */
    public function moderate(Request $request, Post $post)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:warn,hide,delete'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->moderationService->moderatePost($post, (string) auth()->id(), $validated['action']);

        // Notify post author
        if ($post->user && $post->user->id !== auth()->id()) {
            $post->user->notify(new \App\Notifications\PostModeratedNotification(
                $post,
                $validated['action'],
                $validated['reason']
            ));
        }

        // Log action
        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'moderate_post',
            'target_type' => 'post',
            'target_id' => $post->id,
            'old_value' => ['status' => $post->status],
            'new_value' => ['status' => $validated['action'] === 'hide' ? 'moderated' : ($validated['action'] === 'delete' ? 'archived' : $post->status)],
            'notes' => $validated['reason'],
        ]);

        return back()->with('success', 'Post moderated successfully.');
    }

    /**
     * Restore a moderated/archived post.
     */
    public function restore(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $post->status;
        $post->status = 'active';
        $post->save();

        // Notify post author
        if ($post->user && $post->user->id !== auth()->id()) {
            $post->user->notify(new \App\Notifications\PostRestoredNotification(
                $post,
                $validated['reason'] ?? null
            ));
        }

        // Log action
        \App\Models\AuditLog::logAction([
            'admin_id' => auth()->id(),
            'action' => 'restore_post',
            'target_type' => 'post',
            'target_id' => $post->id,
            'old_value' => ['status' => $oldStatus],
            'new_value' => ['status' => 'active'],
            'notes' => $validated['reason'] ?? 'Post restored by admin',
        ]);

        return back()->with('success', 'Post restored successfully.');
    }

    /**
     * Bulk moderate posts.
     */
    public function bulkModerate(Request $request)
    {
        $validated = $request->validate([
            'post_ids' => ['required', 'array'],
            'post_ids.*' => ['required', 'string'],
            'action' => ['required', 'in:warn,hide,delete'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $posts = Post::whereIn('id', $validated['post_ids'])->get();
        $count = 0;

        foreach ($posts as $post) {
            $this->moderationService->moderatePost($post, (string) auth()->id(), $validated['action']);
            $count++;

            // Log action
            \App\Models\AuditLog::logAction([
                'admin_id' => auth()->id(),
                'action' => 'bulk_moderate_post',
                'target_type' => 'post',
                'target_id' => $post->id,
                'notes' => $validated['reason'],
            ]);
        }

        return back()->with('success', "{$count} posts moderated successfully.");
    }
}

