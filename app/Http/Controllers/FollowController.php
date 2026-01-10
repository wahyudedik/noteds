<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use App\Services\FollowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowController extends Controller
{
    public function __construct(
        private FollowService $followService
    ) {}

    /**
     * Follow a user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function follow(Request $request, User $user)
    {
        // Note: FollowPolicy::follow() expects (User $user, User $targetUser)
        // Laravel will automatically pass $request->user() as first param, $user as second
        $this->authorize('follow', $user);

        try {
            $this->followService->follow($request->user(), $user);

            return back()->with('success', 'You are now following ' . $user->name . '.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'follow' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Unfollow a user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unfollow(Request $request, User $user)
    {
        // Note: FollowPolicy::unfollow() expects (User $user, User $targetUser)
        $this->authorize('unfollow', $user);

        try {
            $this->followService->unfollow($request->user(), $user);

            return back()->with('success', 'You have unfollowed ' . $user->name . '.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'unfollow' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get followers list.
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function followers(Request $request, User $user): Response
    {
        $currentUser = $request->user();
        
        if ($currentUser) {
            // Note: FollowPolicy::viewFollowers() expects (User $user, User $targetUser)
            $this->authorize('viewFollowers', $user);
        }

        $followers = Follow::where('following_id', $user->id)
            ->with('follower')
            ->latest()
            ->paginate(20);

        // Get mutual connections for each follower (if viewing own profile or different user)
        $mutualConnectionsMap = [];
        if ($currentUser && $currentUser->id !== $user->id) {
            foreach ($followers->items() as $follow) {
                $followerUser = $follow->follower;
                $mutualConnections = $this->followService->getMutualConnections($currentUser, $followerUser);
                $mutualConnectionsMap[$followerUser->id] = $mutualConnections->count();
            }
        }

        return Inertia::render('Profile/Followers', [
            'user' => $user,
            'followers' => $followers,
            'isFollowing' => $currentUser ? $this->followService->isFollowing($currentUser, $user) : false,
            'mutualConnectionsMap' => $mutualConnectionsMap,
        ]);
    }

    /**
     * Get following list.
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function following(Request $request, User $user): Response
    {
        $currentUser = $request->user();
        
        if ($currentUser) {
            // Note: FollowPolicy::viewFollowing() expects (User $user, User $targetUser)
            $this->authorize('viewFollowing', $user);
        }

        $following = Follow::where('follower_id', $user->id)
            ->with('following')
            ->latest()
            ->paginate(20);

        // Get mutual connections for each following user (if viewing own profile or different user)
        $mutualConnectionsMap = [];
        if ($currentUser && $currentUser->id !== $user->id) {
            foreach ($following->items() as $follow) {
                $followingUser = $follow->following;
                $mutualConnections = $this->followService->getMutualConnections($currentUser, $followingUser);
                $mutualConnectionsMap[$followingUser->id] = $mutualConnections->count();
            }
        }

        return Inertia::render('Profile/Following', [
            'user' => $user,
            'following' => $following,
            'isFollowing' => $currentUser ? $this->followService->isFollowing($currentUser, $user) : false,
            'mutualConnectionsMap' => $mutualConnectionsMap,
        ]);
    }
}

