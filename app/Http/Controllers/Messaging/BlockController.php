<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * Block a user.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'Cannot block yourself.', 'error' => 'Cannot block yourself.'], 400);
        }

        if ($currentUser->hasBlocked($user)) {
            return response()->json(['message' => 'User is already blocked.', 'error' => 'User is already blocked.'], 400);
        }

        BlockedUser::create([
            'user_id' => $currentUser->id,
            'blocked_user_id' => $user->id,
        ]);

        return response()->json(['message' => 'User blocked successfully.']);
    }

    /**
     * Unblock a user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        $blocked = BlockedUser::where('user_id', $currentUser->id)
            ->where('blocked_user_id', $user->id)
            ->first();

        if (!$blocked) {
            return response()->json(['message' => 'User is not blocked.'], 400);
        }

        $blocked->delete();

        return response()->json(['message' => 'User unblocked successfully.']);
    }
}
