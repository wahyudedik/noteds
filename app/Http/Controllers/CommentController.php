<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentMedia;
use App\Models\Post;
use App\Services\CommentEditService;
use App\Services\MentionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function __construct(
        private MentionService $mentionService,
        private CommentEditService $editService
    ) {}

    public function store(Request $request, Post $post): RedirectResponse
    {
        // Check if post is active
        if ($post->status !== 'active') {
            return back()->withErrors(['comment' => 'You cannot comment on this post.']);
        }

        // Privacy: who can comment
        $author = $post->user;
        $viewer = $request->user();
        $perm = $author->settings?->privacy_settings['comments_permission'] ?? 'everyone';
        if ($viewer && $viewer->id !== $author->id) {
            if ($perm === 'none') {
                return back()->withErrors(['comment' => 'Comments are disabled by the author.']);
            }
            if ($perm === 'followers' && !$viewer->isFollowing($author)) {
                return back()->withErrors(['comment' => 'Only followers can comment on this post.']);
            }
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10'],
            'parent_id' => [
                'nullable',
                'exists:comments,id',
                function ($attribute, $value, $fail) use ($post) {
                    if ($value) {
                        $parentComment = \App\Models\Comment::find($value);
                        if (!$parentComment || $parentComment->post_id !== $post->id) {
                            $fail('The parent comment must belong to the same post.');
                        }
                    }
                },
            ],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:2048', 'mimes:jpeg,jpg,png,gif,webp'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    $allowArchives = (bool)config('comments.allow_archives');
                    $allowed = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                        'text/csv',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                    ];
                    if ($allowArchives) {
                        $allowed = array_merge($allowed, [
                            'application/zip',
                            'application/x-zip-compressed',
                            'application/x-rar-compressed',
                            'application/vnd.rar',
                        ]);
                    }
                    if (!in_array(strtolower($value->getClientMimeType()), array_map('strtolower', $allowed))) {
                        $fail('This file type is not allowed.');
                    }
                }
            ],
        ]);

        $comment = null;
        $images = $validated['images'] ?? [];
        $attachments = $validated['attachments'] ?? [];
        unset($validated['images']);
        unset($validated['attachments']);

        // Aggregate size limit for attachments
        if (!empty($attachments)) {
            $totalKb = 0;
            foreach ($attachments as $f) {
                $totalKb += ($f->getSize() / 1024);
            }
            $limitKb = (int) config('comments.max_attachment_total_kb', 25600);
            if ($totalKb > $limitKb) {
                return back()->withErrors(['attachments' => 'Total size of attachments exceeds the limit of ' . $limitKb . 'KB.']);
            }
        }

        DB::transaction(function () use ($request, $post, $validated, $images, $attachments, &$comment) {
            $comment = Comment::create([
                'user_id' => $request->user()->id,
                'post_id' => $post->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
            ]);

            $post->increment('comments_count');

            // Handle image uploads
            if (!empty($images)) {
                $this->storeCommentImages($comment, $images);
            }
            // Handle attachments uploads (docs & images not added above)
            if (!empty($attachments)) {
                $this->storeCommentAttachments($comment, $attachments);
            }

            // Process mentions
            $mentionUsernames = $this->mentionService->extractMentions($validated['content']);
            if (!empty($mentionUsernames)) {
                $this->mentionService->processCommentMentions($comment, $mentionUsernames);
            }

            app(\App\Services\GamificationService::class)->awardAction($request->user(), 'comment_create', [
                'post_id' => $post->id,
                'comment_id' => $comment->id,
            ]);
        });

        return back();
    }

    public function markBestAnswer(Request $request, Comment $comment): RedirectResponse
    {
        // Verify comment exists and has a post relationship
        if (!$comment->post) {
            return back()->withErrors(['error' => 'Comment not found.']);
        }

        // Explicitly verify comment belongs to a valid post (additional safety check)
        // This prevents route model binding manipulation
        if (!$comment->post_id || !$comment->post) {
            return back()->withErrors(['error' => 'Comment does not belong to any post.']);
        }

        // Only post author can mark best answer
        if ($comment->post->user_id !== $request->user()->id) {
            return back()->withErrors(['error' => 'Only the post author can mark best answer.']);
        }

        // Verify post is active
        if ($comment->post->status !== 'active') {
            return back()->withErrors(['error' => 'You cannot mark best answer on an inactive post.']);
        }

        DB::transaction(function () use ($comment) {
            // Unmark other best answers for this post
            Comment::where('post_id', $comment->post_id)
                ->where('id', '!=', $comment->id)
                ->update(['is_best_answer' => false]);

            // Mark this comment as best answer
            $comment->update(['is_best_answer' => true]);
        });

        return back()->with('success', 'Best answer marked successfully.');
    }

    /**
     * Upload image for inline insertion in comment rich text editor.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $image = $request->file('image');
        $fileName = Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $filePath = 'comments/images/temp/' . $fileName;
        
        $image->storeAs('comments/images/temp', $fileName, 'public');
        
        return response()->json([
            'url' => Storage::url($filePath),
            'path' => $filePath,
        ]);
    }

    /**
     * Store images for a comment.
     */
    private function storeCommentImages(Comment $comment, array $images): void
    {
        $order = 0;
        foreach ($images as $image) {
            $extension = $image->getClientOriginalExtension();
            $fileName = Str::uuid() . '_' . time() . '.' . $extension;
            $filePath = 'comments/images/' . $comment->id . '/' . $fileName;

            // Store image
            $image->storeAs('comments/images/' . $comment->id, $fileName, 'public');

            // Create CommentMedia record
            CommentMedia::create([
                'comment_id' => $comment->id,
                'file_path' => $filePath,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $image->getSize(),
                'order' => $order++,
            ]);
        }
    }

    /**
     * Store attachments (documents or other whitelisted files) for a comment.
     */
    private function storeCommentAttachments(Comment $comment, array $attachments): void
    {
        $order = (\App\Models\CommentMedia::where('comment_id', $comment->id)->max('order') ?? -1) + 1;
        foreach ($attachments as $file) {
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::uuid() . '_' . time() . '.' . $extension;
            $dir = 'comments/attachments/' . $comment->id;
            $filePath = $dir . '/' . $fileName;
            $file->storeAs($dir, $fileName, 'public');
            CommentMedia::create([
                'comment_id' => $comment->id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'order' => $order++,
            ]);
            // Dispatch thumbnail job for PDFs
            if (config('comments.pdf_thumbnails') && strtolower($file->getClientMimeType()) === 'application/pdf') {
                $thumbPath = str_replace('comments/attachments/', 'comments/thumbnails/', preg_replace('/\.pdf$/i', '.png', $filePath));
                \App\Jobs\GeneratePdfThumbnail::dispatch($filePath, $thumbPath)->onQueue('default');
            }
        }
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        // Only comment author can edit
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'You can only edit your own comments.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10'],
        ]);

        $this->editService->editComment($comment, $validated, $request->user()->id);

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Get edit history for a comment.
     */
    public function history(Request $request, Comment $comment): Response|JsonResponse
    {
        $history = $this->editService->getEditHistory($comment);

        if ($request->wantsJson()) {
            return response()->json($history);
        }

        return Inertia::render('Comments/History', [
            'comment' => $comment->load('user'),
            'history' => $history,
        ]);
    }

    /**
     * Pin a comment.
     */
    public function pin(Request $request, Comment $comment): RedirectResponse
    {
        // Only post author can pin comments
        if ($comment->post->user_id !== $request->user()->id) {
            abort(403, 'Only the post author can pin comments.');
        }

        $comment->update([
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);

        return back()->with('success', 'Comment pinned successfully.');
    }

    /**
     * Unpin a comment.
     */
    public function unpin(Request $request, Comment $comment): RedirectResponse
    {
        // Only post author can unpin comments
        if ($comment->post->user_id !== $request->user()->id) {
            abort(403, 'Only the post author can unpin comments.');
        }

        $comment->update([
            'is_pinned' => false,
            'pinned_at' => null,
        ]);

        return back()->with('success', 'Comment unpinned successfully.');
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        // Only comment author or post author can delete
        if ($comment->user_id !== $request->user()->id && $comment->post->user_id !== $request->user()->id) {
            abort(403, 'You can only delete your own comments or comments on your posts.');
        }

        $post = $comment->post;

        DB::transaction(function () use ($comment, $post) {
            // Count all descendant comments (replies at any nesting level) that will be deleted via cascade
            // We need to count them before deletion because database cascade doesn't trigger model events
            $totalCommentsToDelete = 1; // Start with the comment being deleted
            
            // Recursively count all nested replies
            // This function counts all descendants of a given comment ID
            $countDescendants = function ($parentId) use (&$countDescendants) {
                $directReplies = Comment::where('parent_id', $parentId)->pluck('id');
                $count = $directReplies->count();
                foreach ($directReplies as $replyId) {
                    $count += $countDescendants($replyId);
                }
                return $count;
            };
            
            $totalCommentsToDelete += $countDescendants($comment->id);

            // Decrement comments_count by the total number of comments being deleted
            $post->decrement('comments_count', $totalCommentsToDelete);

            // Delete the comment (database cascade will handle replies automatically)
            $comment->delete();
        });

        return back()->with('success', 'Comment deleted successfully.');
    }
}
