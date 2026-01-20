<?php

namespace App\Services;

use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BlockService
{
    /**
     * Block a user.
     */
    public function blockUser(User $user, User $userToBlock): BlockedUser
    {
        if ($user->id === $userToBlock->id) {
            throw new \Exception('Cannot block yourself.');
        }

        if ($user->hasBlocked($userToBlock)) {
            throw new \Exception('User is already blocked.');
        }

        return BlockedUser::create([
            'user_id' => $user->id,
            'blocked_user_id' => $userToBlock->id,
        ]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(User $user, User $userToUnblock): void
    {
        $blocked = BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $userToUnblock->id)
            ->first();

        if (!$blocked) {
            throw new \Exception('User is not blocked.');
        }

        $blocked->delete();
    }

    /**
     * Get all blocked users for a user.
     */
    public function getBlockedUsers(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->blockedUsers()->with('blockedUser')->get();
    }
}

