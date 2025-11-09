<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\Note;
use App\Models\User;
use App\Models\Hashtag;
use App\Models\PostMedia;
use App\Services\NotificationService;
use App\Services\HashtagMentionService;
use App\Services\HtmlSanitizer;
use App\Services\PostViewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForumController extends Controller
{
    /**
     * Display forum timeline (all posts from followed users + own posts).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'timeline'); // timeline, all, following
        $search = $request->get('search');

        $query = Post::with(['user', 'note.user', 'parent', 'likes', 'media', 'hashtags', 'bookmarks'])
            ->whereNull('parent_id') // Only top-level posts
            ->where('is_hidden', false)
            ->published()
            ->visibleTo($user)
            ->withCount(['replies', 'allComments']);
        
        // Order: pinned posts first, then by latest (may be overridden later)
        $query->orderBy('is_pinned', 'desc')
            ->orderBy(DB::raw('COALESCE(posts.published_at, posts.created_at)'), 'desc');

        if ($filter === 'trending') {
            $query->whereRaw('COALESCE(posts.published_at, posts.created_at) >= ?', [now()->subDays(7)]);
            $query->addSelect(DB::raw('(
                (posts.likes_count * 4) +
                (posts.comments_count * 5) +
                (posts.shares_count * 6) +
                LEAST(24, GREATEST(0, 48 - TIMESTAMPDIFF(HOUR, COALESCE(posts.published_at, posts.created_at), NOW())))
            ) AS trending_score'));
            $query->orderByDesc('trending_score')
                ->orderByDesc(DB::raw('COALESCE(posts.published_at, posts.created_at)'));
        }
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('note', function($noteQuery) use ($search) {
                      $noteQuery->where('title', 'like', "%{$search}%")
                               ->orWhere('summary', 'like', "%{$search}%");
                  });
            });
        }

        // Apply filters
        if ($filter === 'following' && $user) {
            $followingIds = $user->following()->pluck('following_id')->toArray();
            if (empty($followingIds)) {
                // If not following anyone, show empty result
                $query->whereRaw('1 = 0');
            } else {
                $followingIds[] = $user->id; // Include own posts
                $query->whereIn('user_id', $followingIds);
            }
        } elseif ($filter === 'timeline' && $user) {
            // Timeline: Following + own posts + popular posts
            $followingIds = $user->following()->pluck('following_id')->toArray();
            $followingIds[] = $user->id;
            
            $query->where(function($q) use ($followingIds) {
                $q->whereIn('user_id', $followingIds)
                  ->orWhere('likes_count', '>=', 5) // Popular posts
                  ->orWhere('comments_count', '>=', 3);
            });
        }
        // 'all' shows everything

        if ($filter === 'trending' && empty($search)) {
            $page = LengthAwarePaginator::resolveCurrentPage();
            $cacheKey = sprintf(
                'forum:trending:%s:page:%s',
                $user?->id ?? 'guest',
                $page
            );

            $posts = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query, $page) {
                return $query->paginate(20, ['*'], 'page', $page);
            });

            $posts->appends(['filter' => $filter]);
        } else {
            $posts = $query->paginate(20);
            $posts->appends(array_filter([
                'filter' => $filter,
                'search' => $search,
            ]));
        }

        // Check which posts user has liked and bookmarked
        if ($user) {
            $likedPostIds = $user->likedPosts()->pluck('posts.id')->toArray();
            $bookmarkedPostIds = $user->bookmarkedPosts()->pluck('posts.id')->toArray();
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds, $user) {
                $post->is_liked = in_array($post->id, $likedPostIds) || $post->isLikedBy($user);
                $post->is_bookmarked = in_array($post->id, $bookmarkedPostIds);
                return $post;
            });
        }

        return view('forum.index', compact('posts', 'filter', 'search'));
    }

    /**
     * Show a single post with replies and comments.
     */
    public function show(Request $request, Post $post): View
    {
        $user = auth()->user();
        
        $post->load(['user', 'note', 'parent', 'replies.user', 'allComments.user', 'likes', 'media', 'hashtags', 'mentions']);
        $post->loadCount(['replies', 'allComments']);

        $isOwnerOrAdmin = $user && ($user->id === $post->user_id || $user->hasRole('admin'));

        if ((!$post->is_published || ($post->scheduled_at && $post->scheduled_at->greaterThan(now()))) && !$isOwnerOrAdmin) {
            abort(404);
        }

        if (!$post->canBeViewedBy($user) && !$isOwnerOrAdmin) {
            abort(404);
        }

        if ($post->is_published && (!$post->scheduled_at || $post->scheduled_at->lessThanOrEqualTo(now()))) {
            app(PostViewService::class)->record($post, $user, $request);
        }

        // Check if user has liked
        if ($user) {
            $post->is_liked = $post->isLikedBy($user);
        }

        // Get replies
        $replies = $post->replies()
            ->where('is_hidden', false)
            ->visibleTo($user)
            ->with(['user', 'note', 'likes'])
            ->withCount(['replies', 'allComments'])
            ->latest()
            ->get();

        // Get comments
        $comments = $post->comments()
            ->with(['user', 'replies.user', 'likes'])
            ->withCount(['replies', 'likes'])
            ->latest()
            ->get();

        // Mark which comments user has liked
        if ($user && $comments->count() > 0) {
            $likedCommentIds = $user->likedComments()->pluck('post_comments.id')->toArray();
            $comments->transform(function ($comment) use ($likedCommentIds, $user) {
                $comment->is_liked = in_array($comment->id, $likedCommentIds) || $comment->isLikedBy($user);
                // Also check nested replies
                if ($comment->replies->count() > 0) {
                    $comment->replies->transform(function ($reply) use ($likedCommentIds, $user) {
                        $reply->is_liked = in_array($reply->id, $likedCommentIds) || $reply->isLikedBy($user);
                        return $reply;
                    });
                }
                return $comment;
            });
        }

        return view('forum.show', compact('post', 'replies', 'comments'));
    }

    /**
     * Store a new post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'note_id' => 'nullable|exists:notes,id',
            'parent_id' => 'nullable|exists:posts,id',
            'media.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB per image
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        $sanitizedContent = HtmlSanitizer::sanitize($validated['content']);

        if (HtmlSanitizer::isEmpty($sanitizedContent)) {
            throw ValidationException::withMessages([
                'content' => 'Post content cannot be empty.',
            ]);
        }

        $plainTextLength = mb_strlen(trim(strip_tags($sanitizedContent)));

        if ($plainTextLength > 5000) {
            throw ValidationException::withMessages([
                'content' => 'Post content may not be greater than 5000 characters.',
            ]);
        }

        $visibilityInput = $request->input('visibility', 'public');
        if (!in_array($visibilityInput, ['public', 'followers', 'private'], true)) {
            $visibilityInput = 'public';
        }

        $scheduledAt = null;
        $currentTime = now();
        $isPublished = true;
        $publishedAt = $currentTime;

        $parentPost = null;
        if (!empty($validated['parent_id'])) {
            $parentPost = Post::findOrFail($validated['parent_id']);

            if (!$parentPost->canBeViewedBy(auth()->user())) {
                return redirect()->back()->with('error', 'You cannot reply to this post.');
            }

            $visibility = $parentPost->visibility;
            $scheduledAt = null;
        } else {
            $visibility = $visibilityInput;
            if ($request->filled('scheduled_for')) {
                $scheduledAt = Carbon::parse($request->input('scheduled_for'));
                if ($scheduledAt->greaterThan($currentTime)) {
                    $isPublished = false;
                    $publishedAt = null;
                } else {
                    $scheduledAt = null;
                }
            }
        }

        // If sharing a note, verify user has access
        if (!empty($validated['note_id'])) {
            $note = Note::findOrFail($validated['note_id']);
            
            // Check if note is public or user owns it
            if (!$note->is_public && $note->user_id !== auth()->id()) {
                return redirect()->back()
                    ->with('error', 'You can only share your own notes or public notes.');
            }
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'content' => $sanitizedContent,
            'note_id' => $validated['note_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_published' => $isPublished,
            'scheduled_at' => $isPublished ? null : $scheduledAt,
            'published_at' => $publishedAt,
            'visibility' => $visibility,
        ]);

        // Process hashtags and mentions
        $hashtagMentionService = app(HashtagMentionService::class);
        $hashtagMentionService->processHashtags($post, $sanitizedContent);
        $hashtagMentionService->processMentions($post, $sanitizedContent);

        // Handle media uploads
        if ($request->hasFile('media')) {
            $order = 0;
            foreach ($request->file('media') as $file) {
                $path = $file->store('forum/media', 'public');
                
                PostMedia::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'file_type' => 'image',
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'order' => $order++,
                ]);
            }
        }

        // If it's a reply, increment parent's comments count
        if ($post->parent_id) {
            $parent = Post::find($post->parent_id);
            if ($parent) {
                $parent->increment('comments_count');
            }
        }

        $message = $post->parent_id ? 'Reply posted successfully!' : ($isPublished ? 'Post created successfully!' : 'Post scheduled successfully.');

        if (!$isPublished && is_null($post->parent_id)) {
            return redirect()->route('forum.show', $post)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Like or unlike a post.
     */
    public function like(Post $post): JsonResponse
    {
        $user = auth()->user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This post is not available.',
            ], 403);
        }

        if ($post->isLikedBy($user)) {
            // Unlike
            $post->likes()->detach($user->id);
            $post->decrement('likes_count');
            
            return response()->json([
                'success' => true,
                'liked' => false,
                'likes_count' => $post->fresh()->likes_count,
            ]);
        } else {
            // Like
            $post->likes()->attach($user->id, ['id' => (string) Str::uuid()]);
            $post->increment('likes_count');
            
            // Notify post owner (if not the user themselves)
            if ($post->user_id !== $user->id) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyPostLiked(
                    $post->user,
                    $post->id,
                    $user->name,
                    $post->content
                );
            }
            
            return response()->json([
                'success' => true,
                'liked' => true,
                'likes_count' => $post->fresh()->likes_count,
            ]);
        }
    }

    /**
     * Store a comment on a post.
     */
    public function comment(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:post_comments,id',
        ]);

        $user = auth()->user();
        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This post is not available.',
            ], 403);
        }

        $comment = PostComment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Increment post comments count
        $post->increment('comments_count');

        $notificationService = app(NotificationService::class);

        // If it's a reply to a comment, notify the comment owner
        if ($comment->parent_id) {
            $parentComment = PostComment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $notificationService->notifyCommentReplied(
                    $parentComment->user,
                    $post->id,
                    $user->name,
                    $comment->content
                );
            }
        } else {
            // Notify post owner (if not the user themselves)
            if ($post->user_id !== $user->id) {
                $notificationService->notifyPostCommented(
                    $post->user,
                    $post->id,
                    $user->name,
                    $comment->content
                );
            }
        }

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comment posted successfully!',
        ]);
    }

    /**
     * Update a comment.
     */
    public function updateComment(Request $request, PostComment $comment): JsonResponse
    {
        $user = $request->user();

        if ($user->id !== $comment->user_id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update this comment.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->fresh(),
            'message' => 'Comment updated successfully!',
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroyComment(PostComment $comment): JsonResponse
    {
        $user = auth()->user();

        if ($user->id !== $comment->user_id && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to delete this comment.',
            ], 403);
        }

        $post = $comment->post;
        
        // Decrement post comments count
        $post->decrement('comments_count');

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully!',
        ]);
    }

    /**
     * Like or unlike a comment.
     */
    public function likeComment(PostComment $comment): JsonResponse
    {
        $user = auth()->user();

        $comment->loadMissing('post');
        $parentPost = $comment->post;

        if ($parentPost && !$parentPost->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This comment is not available.',
            ], 403);
        }

        if ($comment->isLikedBy($user)) {
            // Unlike
            $comment->likes()->detach($user->id);
            $comment->decrement('likes_count');
            
            return response()->json([
                'success' => true,
                'liked' => false,
                'likes_count' => max(0, $comment->fresh()->likes_count),
            ]);
        } else {
            // Like
            $comment->likes()->attach($user->id, ['id' => (string) Str::uuid()]);
            $comment->increment('likes_count');
            
            // Notify comment owner (if not the user themselves)
            if ($comment->user_id !== $user->id) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyCommentLiked(
                    $comment->user,
                    $comment->post_id,
                    $user->name
                );
            }
            
            return response()->json([
                'success' => true,
                'liked' => true,
                'likes_count' => $comment->fresh()->likes_count,
            ]);
        }
    }

    /**
     * Update a post.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'note_id' => 'nullable|exists:notes,id',
            'scheduled_for' => 'nullable|date|after:now',
        ]);

        $sanitizedContent = HtmlSanitizer::sanitize($validated['content']);

        if (HtmlSanitizer::isEmpty($sanitizedContent)) {
            throw ValidationException::withMessages([
                'content' => 'Post content cannot be empty.',
            ]);
        }

        $plainTextLength = mb_strlen(trim(strip_tags($sanitizedContent)));

        if ($plainTextLength > 5000) {
            throw ValidationException::withMessages([
                'content' => 'Post content may not be greater than 5000 characters.',
            ]);
        }

        $visibility = $post->visibility;
        if (is_null($post->parent_id)) {
            $visibilityInput = $request->input('visibility', $post->visibility);
            if (!in_array($visibilityInput, ['public', 'followers', 'private'], true)) {
                $visibilityInput = 'public';
            }
            $visibility = $visibilityInput;
        }

        // If sharing a note, verify user has access
        if (!empty($validated['note_id'])) {
            $note = Note::findOrFail($validated['note_id']);
            
            // Check if note is public or user owns it
            if (!$note->is_public && $note->user_id !== auth()->id()) {
                return redirect()->back()
                    ->with('error', 'You can only share your own notes or public notes.');
            }

            // Only allow sharing active notes
            if ($note->status !== 'active') {
                return redirect()->back()
                    ->with('error', 'You can only share active notes.');
            }
        }

        $scheduledAt = null;
        $currentTime = now();
        $isPublished = $post->is_published;
        $publishedAt = $post->published_at ?? $currentTime;

        if (!$post->parent_id) {
            if ($request->filled('scheduled_for')) {
                $scheduledAt = Carbon::parse($request->input('scheduled_for'));
                if ($scheduledAt->greaterThan($currentTime)) {
                    $isPublished = false;
                    $publishedAt = null;
                } else {
                    $scheduledAt = null;
                    $isPublished = true;
                    $publishedAt = $currentTime;
                }
            } else {
                $scheduledAt = null;
                $isPublished = true;
                if (!$post->published_at) {
                    $publishedAt = $currentTime;
                }
            }
        }

        $post->update([
            'content' => $sanitizedContent,
            'note_id' => $validated['note_id'] ?? null,
            'visibility' => $visibility,
            'is_published' => $post->parent_id ? true : $isPublished,
            'scheduled_at' => $post->parent_id ? null : $scheduledAt,
            'published_at' => $post->parent_id ? ($post->published_at ?? $currentTime) : $publishedAt,
        ]);

        // Process hashtags and mentions for updated content
        $hashtagMentionService = app(HashtagMentionService::class);
        $hashtagMentionService->processHashtags($post, $sanitizedContent);
        $hashtagMentionService->processMentions($post, $sanitizedContent);

        $message = (!$post->parent_id && !$isPublished) ? 'Post rescheduled successfully.' : 'Post updated successfully.';

        if (!$post->parent_id && !$isPublished) {
            return redirect()->route('forum.show', $post)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Show posts by hashtag.
     */
    public function hashtag(string $slug): View
    {
        $hashtag = Hashtag::where('slug', $slug)->firstOrFail();
        $user = auth()->user();
        
        $posts = $hashtag->posts()
            ->whereNull('parent_id')
            ->where('posts.is_hidden', false)
            ->published()
            ->with(['user', 'note.user', 'likes', 'media', 'hashtags', 'bookmarks'])
            ->withCount(['replies', 'allComments'])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);
        
        // Mark if user has bookmarked each post
        if ($user) {
            $likedPostIds = $user->likedPosts()->pluck('posts.id')->toArray();
            $bookmarkedPostIds = $user->bookmarkedPosts()->pluck('posts.id')->toArray();
            $posts->getCollection()->transform(function ($post) use ($likedPostIds, $bookmarkedPostIds, $user) {
                $post->is_liked = in_array($post->id, $likedPostIds) || $post->isLikedBy($user);
                $post->is_bookmarked = in_array($post->id, $bookmarkedPostIds);
                return $post;
            });
        }

        return view('forum.hashtag', compact('hashtag', 'posts'));
    }

    /**
     * Share a post (increment share count).
     */
    public function share(Post $post): JsonResponse
    {
        $user = auth()->user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This post is not available.',
            ], 403);
        }

        $post->increment('shares_count');

        return response()->json([
            'success' => true,
            'shares_count' => $post->fresh()->shares_count,
        ]);
    }

    /**
     * Delete a post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        // Decrement parent's comments count if this is a reply
        if ($post->parent_id) {
            $parent = Post::find($post->parent_id);
            if ($parent) {
                $parent->decrement('comments_count');
            }
        }

        $post->delete();

        return redirect()->route('forum.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Pin or unpin a post.
     */
    public function pin(Post $post): JsonResponse
    {
        $this->authorize('pin', $post);

        // Limit: user can only have 3 pinned posts
        $userPinnedCount = Post::where('user_id', auth()->id())
            ->where('is_pinned', true)
            ->where('id', '!=', $post->id)
            ->count();

        if ($post->is_pinned) {
            // Unpin
            $post->update(['is_pinned' => false]);
            $message = 'Post unpinned successfully.';
            $pinned = false;
        } else {
            // Pin (check limit)
            if ($userPinnedCount >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only pin up to 3 posts. Please unpin another post first.',
                ], 422);
            }
            $post->update(['is_pinned' => true]);
            $message = 'Post pinned successfully.';
            $pinned = true;
        }

        return response()->json([
            'success' => true,
            'pinned' => $pinned,
            'message' => $message,
        ]);
    }
}

