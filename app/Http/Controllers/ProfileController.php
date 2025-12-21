<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AnalyticsService;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}
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

        // Analytics data (only for own profile)
        $stats = null;
        $engagementData = null;
        $topPosts = null;

        if ($isOwnProfile) {
            $stats = $this->analyticsService->getUserStats($profileUser);
            $engagementData = $this->analyticsService->getEngagementData($profileUser, 30);
            $topPosts = $this->analyticsService->getTopPosts($profileUser, 5);
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
