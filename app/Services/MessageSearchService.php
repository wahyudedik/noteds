<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageSearchService
{
    /**
     * Search messages in a conversation.
     */
    public function search(Conversation $conversation, User $user, array $filters = []): LengthAwarePaginator
    {
        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            throw new \Exception('User is not a participant of this conversation.');
        }

        $query = $conversation->messages();

        // Search by content
        if (!empty($filters['q'])) {
            $query->where('content', 'like', '%' . $filters['q'] . '%');
        }

        // Filter by sender
        if (!empty($filters['sender_id'])) {
            $query->where('user_id', $filters['sender_id']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with(['user', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }
}

