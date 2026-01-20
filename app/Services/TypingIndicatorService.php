<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TypingIndicatorService
{
    private const CACHE_TTL = 5; // 5 seconds
    private const CACHE_PREFIX = 'typing:conversation:';

    /**
     * Set typing indicator for user in conversation.
     */
    public function setTyping(Conversation $conversation, User $user): void
    {
        $key = $this->getCacheKey($conversation->id, $user->id);
        Cache::put($key, now()->toIso8601String(), self::CACHE_TTL);
    }

    /**
     * Remove typing indicator for user in conversation.
     */
    public function removeTyping(Conversation $conversation, User $user): void
    {
        $key = $this->getCacheKey($conversation->id, $user->id);
        Cache::forget($key);
    }

    /**
     * Get all typing users in conversation.
     */
    public function getTypingUsers(Conversation $conversation, ?User $currentUser = null): array
    {
        $participants = $conversation->activeParticipants()
            ->where('user_id', '!=', $currentUser?->id)
            ->get();

        $typingUsers = [];

        foreach ($participants as $participant) {
            $key = $this->getCacheKey($conversation->id, $participant->user_id);
            if (Cache::has($key)) {
                $typingUsers[] = [
                    'user_id' => $participant->user_id,
                    'user' => $participant->user,
                ];
            }
        }

        return $typingUsers;
    }

    /**
     * Check if user is typing in conversation.
     */
    public function isTyping(Conversation $conversation, User $user): bool
    {
        $key = $this->getCacheKey($conversation->id, $user->id);
        return Cache::has($key);
    }

    /**
     * Get cache key for typing indicator.
     */
    protected function getCacheKey(string $conversationId, string $userId): string
    {
        return self::CACHE_PREFIX . $conversationId . ':user:' . $userId;
    }
}

