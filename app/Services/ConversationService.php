<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Create or get existing direct conversation between two users.
     */
    public function createDirectConversation(User $user1, User $user2): Conversation
    {
        // Check if conversation already exists
        $existingConversation = Conversation::where('type', 'direct')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id)->whereNull('left_at');
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id)->whereNull('left_at');
            })
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        // Check if users are blocked
        if ($user1->hasBlocked($user2) || $user2->hasBlocked($user1)) {
            throw new \Exception('Cannot create conversation with blocked user.');
        }

        return DB::transaction(function () use ($user1, $user2) {
            $conversation = Conversation::create([
                'type' => 'direct',
                'created_by' => $user1->id,
            ]);

            // Add both users as participants
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user1->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user2->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            return $conversation;
        });
    }

    /**
     * Create a group conversation.
     */
    public function createGroupConversation(User $creator, string $name, array $participantIds, ?string $description = null, ?string $avatar = null): Conversation
    {
        if (count($participantIds) < 1) {
            throw new \Exception('Group conversation must have at least 2 participants.');
        }

        // Check if creator has blocked any participants
        $blockedUsers = $creator->blockedUsers()
            ->whereIn('blocked_user_id', $participantIds)
            ->pluck('blocked_user_id')
            ->toArray();

        if (!empty($blockedUsers)) {
            throw new \Exception('Cannot add blocked users to conversation.');
        }

        return DB::transaction(function () use ($creator, $name, $participantIds, $description, $avatar) {
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $name,
                'description' => $description,
                'avatar' => $avatar,
                'created_by' => $creator->id,
            ]);

            // Add creator as admin
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $creator->id,
                'role' => 'admin',
                'joined_at' => now(),
            ]);

            // Add other participants
            foreach ($participantIds as $userId) {
                if ($userId !== $creator->id) {
                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $userId,
                        'role' => 'member',
                        'joined_at' => now(),
                    ]);
                }
            }

            return $conversation;
        });
    }

    /**
     * Add participant to group conversation.
     */
    public function addParticipant(Conversation $conversation, User $user, User $addedBy): void
    {
        if ($conversation->type !== 'group') {
            throw new \Exception('Can only add participants to group conversations.');
        }

        // Check if addedBy is admin
        $participant = $conversation->participants()
            ->where('user_id', $addedBy->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant || !$participant->isAdmin()) {
            throw new \Exception('Only admins can add participants.');
        }

        // Check if user is already a participant
        if ($conversation->hasParticipant($user)) {
            throw new \Exception('User is already a participant.');
        }

        // Check if user is blocked
        if ($addedBy->hasBlocked($user)) {
            throw new \Exception('Cannot add blocked user.');
        }

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove participant from group conversation.
     */
    public function removeParticipant(Conversation $conversation, User $user, User $removedBy): void
    {
        if ($conversation->type !== 'group') {
            throw new \Exception('Can only remove participants from group conversations.');
        }

        // Check if removedBy is admin or removing themselves
        if ($user->id !== $removedBy->id) {
            $participant = $conversation->participants()
                ->where('user_id', $removedBy->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant || !$participant->isAdmin()) {
                throw new \Exception('Only admins can remove participants.');
            }
        }

        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not a participant.');
        }

        $participant->update([
            'left_at' => now(),
        ]);
    }

    /**
     * Archive conversation for user.
     */
    public function archiveConversation(Conversation $conversation, User $user): void
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not a participant.');
        }

        $participant->update([
            'archived_at' => now(),
        ]);
    }

    /**
     * Unarchive conversation for user.
     */
    public function unarchiveConversation(Conversation $conversation, User $user): void
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not a participant.');
        }

        $participant->update([
            'archived_at' => null,
        ]);
    }

    /**
     * Mute conversation for user.
     */
    public function muteConversation(Conversation $conversation, User $user, ?\DateTime $until = null): void
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not a participant.');
        }

        $participant->update([
            'muted_until' => $until ?? now()->addDays(30),
        ]);
    }

    /**
     * Unmute conversation for user.
     */
    public function unmuteConversation(Conversation $conversation, User $user): void
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not a participant.');
        }

        $participant->update([
            'muted_until' => null,
        ]);
    }
}

