<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $request->user()->loadMissing([]),
        ]);
    }

    /**
     * Display the user's profile.
     */
    public function show(Request $request, User $user = null): Response
    {
        $profileUser = $user ?? $request->user();
        $isOwnProfile = $request->user() && $request->user()->id === $profileUser->id;
        
        // Get user's posts
        $posts = \App\Models\Post::where('user_id', $profileUser->id)
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->get();

        // Get user votes for posts
        $userVotes = [];
        if ($request->user()) {
            $postIds = $posts->pluck('id');
            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->get()
                ->keyBy('post_id');
            
            foreach ($posts as $post) {
                $vote = $votes->get($post->id);
                $userVotes[$post->id] = $vote ? $vote->vote_type : null;
            }
        }

        // Analytics data (only for own profile or if needed)
        $stats = null;
        $engagementData = null;
        $topPosts = null;
        
        if ($isOwnProfile) {
            $stats = [
                'total_posts' => \App\Models\Post::where('user_id', $profileUser->id)->count(),
                'total_comments' => \App\Models\Comment::where('user_id', $profileUser->id)->count(),
                'total_upvotes' => \App\Models\PostVote::whereHas('post', function ($q) use ($profileUser) {
                    $q->where('user_id', $profileUser->id);
                })->where('vote_type', 'upvote')->count(),
                'total_downvotes' => \App\Models\PostVote::whereHas('post', function ($q) use ($profileUser) {
                    $q->where('user_id', $profileUser->id);
                })->where('vote_type', 'downvote')->count(),
                'engagement_rate' => 0,
                'posts_last_30_days' => \App\Models\Post::where('user_id', $profileUser->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count(),
            ];
            
            $totalEngagement = $stats['total_upvotes'] + $stats['total_comments'];
            $stats['engagement_rate'] = $stats['total_posts'] > 0 
                ? round(($totalEngagement / $stats['total_posts']) * 100, 2) 
                : 0;

            $last30Days = now()->subDays(30);
            $engagementData = \App\Models\Post::where('user_id', $profileUser->id)
                ->where('created_at', '>=', $last30Days)
                ->get()
                ->map(function ($post) {
                    return [
                        'date' => $post->created_at->format('Y-m-d'),
                        'upvotes' => $post->upvotes_count,
                        'comments' => $post->comments_count,
                    ];
                })
                ->groupBy('date')
                ->map(function ($items) {
                    return [
                        'upvotes' => $items->sum('upvotes'),
                        'comments' => $items->sum('comments'),
                    ];
                });

            $topPosts = \App\Models\Post::where('user_id', $profileUser->id)
                ->orderByDesc(\Illuminate\Support\Facades\DB::raw('upvotes_count + comments_count'))
                ->limit(5)
                ->with('user')
                ->get();
        }
        
        return Inertia::render('Profile/Show', [
            'profileUser' => $profileUser,
            'isOwnProfile' => $isOwnProfile,
            'posts' => $posts,
            'userVotes' => $userVotes,
            'stats' => $stats,
            'engagement_data' => $engagementData,
            'top_posts' => $topPosts,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
