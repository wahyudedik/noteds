<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteComment;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteCommentController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Note $note): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'exists:note_comments,id'],
        ]);

        $comment = NoteComment::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // Notify note owner (if not the commenter)
        if ($note->user_id !== auth()->id()) {
            $this->notificationService->create(
                $note->user,
                'note_commented',
                '💬 New Comment',
                auth()->user()->name . ' commented on your note: ' . $note->title,
                route('marketplace.show', $note) . '#comment-' . $comment->id,
                ['comment_id' => $comment->id, 'note_id' => $note->id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
                'message' => 'Comment added successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Reply to a comment.
     */
    public function reply(Request $request, NoteComment $comment): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $reply = NoteComment::create([
            'note_id' => $comment->note_id,
            'user_id' => auth()->id(),
            'parent_id' => $comment->id,
            'content' => $validated['content'],
        ]);

        // Notify parent comment owner (if not the replier)
        if ($comment->user_id !== auth()->id()) {
            $this->notificationService->create(
                $comment->user,
                'comment_replied',
                '💬 Reply to Your Comment',
                auth()->user()->name . ' replied to your comment',
                route('marketplace.show', $comment->note) . '#comment-' . $reply->id,
                ['comment_id' => $reply->id, 'parent_id' => $comment->id]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $reply->load('user'),
                'message' => 'Reply added successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $comment->note)
            ->with('success', 'Reply added successfully.');
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, NoteComment $comment): RedirectResponse|JsonResponse
    {
        // Ensure user owns this comment
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $comment->update([
            'content' => $validated['content'],
            'is_edited' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
                'message' => 'Comment updated successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $comment->note)
            ->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(NoteComment $comment): RedirectResponse|JsonResponse
    {
        // Ensure user owns this comment or is admin
        if ($comment->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $note = $comment->note;
        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully.',
            ]);
        }

        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Comment deleted successfully.');
    }

    /**
     * Like/unlike a comment.
     */
    public function like(NoteComment $comment): JsonResponse
    {
        // Toggle like (simple implementation - can be enhanced with separate likes table)
        $comment->incrementLikes();

        return response()->json([
            'success' => true,
            'like_count' => $comment->like_count,
            'message' => 'Comment liked.',
        ]);
    }
}
