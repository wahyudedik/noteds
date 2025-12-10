<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumDiscussion;
use App\Models\ForumComment;
use App\Models\ForumFlag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminForumController extends Controller
{
    /**
     * Display forum discussions
     *
     * @param Request $request
     * @return View
     */
    public function discussions(Request $request): View
    {
        $this->authorize('moderate-forum');

        $query = ForumDiscussion::with('author', 'category');

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('title', 'like', "%$search%")
                ->orWhere('content', 'like', "%$search%");
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $discussions = $query->latest('created_at')->paginate(15);

        $stats = [
            'total' => ForumDiscussion::count(),
            'active' => ForumDiscussion::where('status', 'active')->count(),
            'locked' => ForumDiscussion::where('status', 'locked')->count(),
            'archived' => ForumDiscussion::where('status', 'archived')->count(),
        ];

        return view('admin.data-management.forum', [
            'discussions' => $discussions,
            'stats' => $stats,
            'activeTab' => 'discussions',
        ]);
    }

    /**
     * Display forum comments for moderation
     *
     * @param Request $request
     * @return View
     */
    public function comments(Request $request): View
    {
        $this->authorize('moderate-forum');

        $query = ForumComment::with('author', 'discussion');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('content', 'like', "%$search%");
        }

        $comments = $query->latest('created_at')->paginate(15);

        $stats = [
            'total' => ForumComment::count(),
            'approved' => ForumComment::where('status', 'approved')->count(),
            'pending' => ForumComment::where('status', 'pending')->count(),
            'rejected' => ForumComment::where('status', 'rejected')->count(),
        ];

        return view('admin.data-management.forum', [
            'comments' => $comments,
            'stats' => $stats,
            'activeTab' => 'comments',
        ]);
    }

    /**
     * Display flagged content
     *
     * @param Request $request
     * @return View
     */
    public function flagged(Request $request): View
    {
        $this->authorize('moderate-forum');

        $query = ForumFlag::with('discussion', 'comment', 'user');

        // Filter by reason
        if ($request->has('reason') && $request->reason !== '') {
            $query->where('reason', $request->reason);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $flags = $query->latest('created_at')->paginate(15);

        $stats = [
            'total' => ForumFlag::count(),
            'pending' => ForumFlag::where('status', 'pending')->count(),
            'resolved' => ForumFlag::where('status', 'resolved')->count(),
            'dismissed' => ForumFlag::where('status', 'dismissed')->count(),
            'spam' => ForumFlag::where('reason', 'spam')->count(),
            'offensive' => ForumFlag::where('reason', 'offensive')->count(),
            'inappropriate' => ForumFlag::where('reason', 'inappropriate')->count(),
        ];

        return view('admin.data-management.forum', [
            'flags' => $flags,
            'stats' => $stats,
            'activeTab' => 'flagged',
        ]);
    }

    /**
     * Delete discussion
     *
     * @param ForumDiscussion $discussion
     * @return RedirectResponse
     */
    public function deleteDiscussion(ForumDiscussion $discussion): RedirectResponse
    {
        $this->authorize('moderate-forum');

        activity('admin')
            ->performedOn($discussion)
            ->log('Forum discussion deleted');

        $discussion->delete();

        return redirect()->back()->with('success', 'Diskusi berhasil dihapus');
    }

    /**
     * Lock discussion
     *
     * @param ForumDiscussion $discussion
     * @return RedirectResponse
     */
    public function lockDiscussion(ForumDiscussion $discussion): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $discussion->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);

        activity('admin')
            ->performedOn($discussion)
            ->log('Forum discussion locked');

        return redirect()->back()->with('success', 'Diskusi berhasil dikunci');
    }

    /**
     * Unlock discussion
     *
     * @param ForumDiscussion $discussion
     * @return RedirectResponse
     */
    public function unlockDiscussion(ForumDiscussion $discussion): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $discussion->update([
            'status' => 'active',
            'locked_at' => null,
        ]);

        activity('admin')
            ->performedOn($discussion)
            ->log('Forum discussion unlocked');

        return redirect()->back()->with('success', 'Diskusi berhasil dibuka');
    }

    /**
     * Approve comment
     *
     * @param ForumComment $comment
     * @return RedirectResponse
     */
    public function approveComment(ForumComment $comment): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $comment->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        activity('admin')
            ->performedOn($comment)
            ->log('Forum comment approved');

        return redirect()->back()->with('success', 'Komentar berhasil disetujui');
    }

    /**
     * Reject comment
     *
     * @param Request $request
     * @param ForumComment $comment
     * @return RedirectResponse
     */
    public function rejectComment(Request $request, ForumComment $comment): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $comment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        activity('admin')
            ->performedOn($comment)
            ->withProperties(['reason' => $request->reason])
            ->log('Forum comment rejected');

        return redirect()->back()->with('success', 'Komentar berhasil ditolak');
    }

    /**
     * Delete comment
     *
     * @param ForumComment $comment
     * @return RedirectResponse
     */
    public function deleteComment(ForumComment $comment): RedirectResponse
    {
        $this->authorize('moderate-forum');

        activity('admin')
            ->performedOn($comment)
            ->log('Forum comment deleted');

        $comment->delete();

        return redirect()->back()->with('success', 'Komentar berhasil dihapus');
    }

    /**
     * Resolve flag
     *
     * @param Request $request
     * @param ForumFlag $flag
     * @return RedirectResponse
     */
    public function resolveFlag(Request $request, ForumFlag $flag): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $request->validate([
            'action' => 'required|in:delete,approve,dismiss',
        ]);

        $flag->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'action_taken' => $request->action,
        ]);

        // Take action based on admin decision
        if ($request->action === 'delete') {
            if ($flag->discussion_id) {
                ForumDiscussion::find($flag->discussion_id)?->delete();
            } elseif ($flag->comment_id) {
                ForumComment::find($flag->comment_id)?->delete();
            }
        } elseif ($request->action === 'approve') {
            if ($flag->discussion_id) {
                ForumDiscussion::find($flag->discussion_id)?->update(['status' => 'active']);
            } elseif ($flag->comment_id) {
                ForumComment::find($flag->comment_id)?->update(['status' => 'approved']);
            }
        }

        activity('admin')
            ->performedOn($flag)
            ->withProperties(['action' => $request->action])
            ->log('Forum flag resolved');

        return redirect()->back()->with('success', 'Flag berhasil diselesaikan');
    }

    /**
     * Dismiss flag
     *
     * @param ForumFlag $flag
     * @return RedirectResponse
     */
    public function dismissFlag(ForumFlag $flag): RedirectResponse
    {
        $this->authorize('moderate-forum');

        $flag->update([
            'status' => 'dismissed',
            'dismissed_at' => now(),
        ]);

        activity('admin')
            ->performedOn($flag)
            ->log('Forum flag dismissed');

        return redirect()->back()->with('success', 'Flag berhasil ditutup');
    }
}
