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
        $this->followService->unfollow($request->user(), $user);

        return back()->with('success', 'You have unfollowed ' . $user->name . '.');
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
        $followers = Follow::where('following_id', $user->id)
            ->with('follower')
            ->latest()
            ->paginate(20);

        return Inertia::render('Profile/Followers', [
            'user' => $user,
            'followers' => $followers,
            'isFollowing' => $request->user() ? $this->followService->isFollowing($request->user(), $user) : false,
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
        $following = Follow::where('follower_id', $user->id)
            ->with('following')
            ->latest()
            ->paginate(20);

        return Inertia::render('Profile/Following', [
            'user' => $user,
            'following' => $following,
            'isFollowing' => $request->user() ? $this->followService->isFollowing($request->user(), $user) : false,
        ]);
    }
}

