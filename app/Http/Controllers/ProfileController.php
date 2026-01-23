<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AnalyticsService;
use App\Models\Post;
use App\Models\PostVote;
use App\Models\User;
use App\Models\BrandRegistration;
use App\Models\ClipperProfile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
        $user = $request->user();
        $userArray = $user->toArray();
        $userArray['avatar_url'] = $user->avatar_url;

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $userArray,
        ]);
    }

    /**
     * Display the user's profile.
     */
    public function show(Request $request, User $user = null): Response
    {
        $profileUser = $user ?? $request->user();
        
        // Ensure we have a valid user
        if (!$profileUser) {
            abort(404, 'User not found');
        }
        
        $isOwnProfile = $request->user() && $request->user()->id === $profileUser->id;

        // Get user's posts
        $posts = \App\Models\Post::where('user_id', $profileUser->id)
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->get();

        // Get user votes and bookmarks for posts
        $userVotes = [];
        $userBookmarks = [];
        if ($request->user()) {
            $postIds = $posts->pluck('id');
            
            // Get votes
            $votes = \App\Models\PostVote::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->get()
                ->keyBy('post_id');

            foreach ($posts as $post) {
                $vote = $votes->get($post->id);
                $userVotes[$post->id] = $vote ? $vote->vote_type : null;
            }

            // Get bookmarks
            $bookmarks = \App\Models\Bookmark::where('user_id', $request->user()->id)
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->toArray();

            foreach ($posts as $post) {
                $userBookmarks[$post->id] = in_array($post->id, $bookmarks);
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

        // Add avatar_url to profileUser
        $profileUserArray = $profileUser->toArray();
        $profileUserArray['avatar_url'] = $profileUser->avatar_url;

        // Get following status
        $isFollowing = false;
        $mutualConnections = null;
        $mutualConnectionsCount = 0;
        if ($request->user() && !$isOwnProfile) {
            $isFollowing = $request->user()->following()->where('following_id', $profileUser->id)->exists();
            // Get mutual connections
            $followService = app(\App\Services\FollowService::class);
            $mutualConnections = $followService->getMutualConnections($request->user(), $profileUser);
            $mutualConnectionsCount = $mutualConnections->count();
        }

        // Get brand registration if exists
        $brandRegistration = null;
        if ($profileUser->clipper_role === 'brand' || $profileUser->role === 'brand') {
            $brandRegistration = BrandRegistration::where('user_id', $profileUser->id)
                ->where('status', 'approved')
                ->first();
        }

        // Get clipper profile if exists
        $clipperProfile = null;
        if ($profileUser->clipper_role === 'clipper' || $profileUser->role === 'clipper') {
            $clipperProfile = ClipperProfile::where('user_id', $profileUser->id)
                ->where('status', 'verified')
                ->first();
        }

        return Inertia::render('Profile/Show', [
            'profileUser' => $profileUserArray,
            'isOwnProfile' => $isOwnProfile,
            'isFollowing' => $isFollowing,
            'mutualConnectionsCount' => $mutualConnectionsCount,
            'mutualConnections' => $mutualConnections ? $mutualConnections->take(5)->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->business_name ?? $u->name,
                'avatar_url' => $u->avatar_url,
            ]) : [],
            'posts' => $posts,
            'userVotes' => $userVotes,
            'userBookmarks' => $userBookmarks,
            'brandRegistration' => $brandRegistration,
            'clipperProfile' => $clipperProfile,
            'stats' => $stats,
            'engagement_data' => $engagementData,
            'top_posts' => $topPosts,
            'verifications' => \App\Models\UserVerification::where('user_id', $profileUser->id)->with('type')->get()->map(fn($v) => [
                'type' => $v->type?->name,
                'slug' => $v->type?->slug,
                'icon' => $v->type?->badge_icon,
                'verified_at' => $v->verified_at,
            ]),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Fill other validated fields (exclude avatar and is_verified_mentor from fillable)
        // is_verified_mentor can only be set by admin, not by user
        $validated = $request->safe()->except(['avatar', 'is_verified_mentor']);
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
