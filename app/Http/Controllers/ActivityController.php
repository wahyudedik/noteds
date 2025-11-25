<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLike;
use App\Models\ActivityComment;
use App\Models\ActivityShare;
use App\Events\ActivityLiked;
use App\Events\ActivityCommented;
use App\Events\ActivityShared;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display activity feed.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        $activities = Activity::with([
            'user',
            'subject',
            'likes' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            },
            'likes.user',
            'comments.user',
            'comments.replies.user',
        ])
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Get available activity types for filter
        $activityTypes = Activity::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('activity.index', compact('activities', 'activityTypes'));
    }

    /**
     * Display activity feed for followed users.
     */
    public function following(Request $request): View
    {
        $user = auth()->user();
        $followingIds = $user->following()->pluck('following_id');

        $activities = Activity::whereIn('user_id', $followingIds)
            ->with([
                'user',
                'subject',
                'likes' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'likes.user',
                'comments.user',
                'comments.replies.user',
            ])
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $activityTypes = Activity::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('activity.following', compact('activities', 'activityTypes'));
    }

    /**
     * Like an activity
     */
    public function like(Activity $activity): JsonResponse
    {
        $user = auth()->user();

        if ($activity->isLikedBy($user)) {
            // Unlike
            $activity->likes()->where('user_id', $user->id)->delete();
            $liked = false;
        } else {
            // Like
            ActivityLike::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        $activity->refresh();
        $likesCount = $activity->likes_count;

        // Broadcast real-time update
        event(new ActivityLiked($activity, $likesCount, $liked));

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }

    /**
     * Comment on an activity
     */
    public function comment(Request $request, Activity $activity): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|uuid|exists:activity_comments,id',
        ]);

        $comment = ActivityComment::create([
            'activity_id' => $activity->id,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load(['user', 'parent.user']);

        $activity->refresh();
        $commentsCount = $activity->comments_count;

        // Broadcast real-time update
        event(new ActivityCommented($activity, $commentsCount));

        return response()->json([
            'comment' => $comment,
            'comments_count' => $commentsCount,
        ]);
    }

    /**
     * Share an activity
     */
    public function share(Request $request, Activity $activity): JsonResponse
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|in:facebook,twitter,linkedin,copy_link',
            'message' => 'nullable|string|max:500',
        ]);

        $share = ActivityShare::create([
            'activity_id' => $activity->id,
            'user_id' => auth()->id(),
            'platform' => $validated['platform'] ?? 'copy_link',
            'message' => $validated['message'] ?? null,
        ]);

        // Generate share URL
        $shareUrl = route('activity.show', $activity) . '?shared=true';

        $activity->refresh();
        $sharesCount = $activity->shares_count;

        // Broadcast real-time update
        event(new ActivityShared($activity, $sharesCount));

        return response()->json([
            'share' => $share,
            'share_url' => $shareUrl,
            'shares_count' => $sharesCount,
        ]);
    }

    /**
     * Get activity details (for AJAX)
     */
    public function show(Activity $activity): JsonResponse
    {
        $activity->load([
            'user',
            'subject',
            'likes.user',
            'comments.user',
            'comments.replies.user',
        ]);

        return response()->json([
            'activity' => $activity,
            'is_liked' => $activity->isLikedBy(auth()->user()),
            'likes_count' => $activity->likes_count,
            'comments_count' => $activity->comments_count,
            'shares_count' => $activity->shares_count,
        ]);
    }
}
