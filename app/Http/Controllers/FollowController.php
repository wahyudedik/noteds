<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function follow(User $user): JsonResponse|RedirectResponse
    {
        $follower = auth()->user();

        // Prevent self-follow
        if ($follower->id === $user->id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot follow yourself.',
                ], 400);
            }
            return redirect()->back()->with('error', 'You cannot follow yourself.');
        }

        if ($follower->isFollowing($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already following this user.',
                ], 400);
            }
            return redirect()->back()->with('error', 'You are already following this user.');
        }

        $follower->following()->attach($user->id, ['id' => (string) Str::uuid()]);

        // Notify the user being followed
        $notificationService = app(NotificationService::class);
        $notificationService->notifyNewFollower(
            $user,
            $follower->name
        );

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'You are now following ' . $user->name,
                'following' => true,
            ]);
        }

        return redirect()->back()->with('success', 'You are now following ' . $user->name);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $user): JsonResponse|RedirectResponse
    {
        $follower = auth()->user();

        if (!$follower->isFollowing($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this user.',
                ], 400);
            }
            return redirect()->back()->with('error', 'You are not following this user.');
        }

        $follower->following()->detach($user->id);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'You unfollowed ' . $user->name,
                'following' => false,
            ]);
        }

        return redirect()->back()->with('success', 'You unfollowed ' . $user->name);
    }
}

