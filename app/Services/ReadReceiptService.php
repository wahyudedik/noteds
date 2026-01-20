<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\ReadReceipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReadReceiptService
{
    /**
     * Create a read receipt for a message.
     */
    public function createReadReceipt(Message $message, User $user): ReadReceipt
    {
        // Check if already exists
        $existing = ReadReceipt::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return ReadReceipt::create([
            'message_id' => $message->id,
            'user_id' => $user->id,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all messages in conversation as read for user.
     */
    public function markConversationAsRead(Conversation $conversation, User $user): void
    {
        // Get all unread messages
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            return;
        }

        $lastReadAt = $participant->last_read_at;

        $unreadMessages = $conversation->messages()
            ->where('is_deleted', false)
            ->where('user_id', '!=', $user->id)
            ->when($lastReadAt, function ($query) use ($lastReadAt) {
                $query->where('created_at', '>', $lastReadAt);
            })
            ->get();

        // Create read receipts for all unread messages
        DB::transaction(function () use ($unreadMessages, $user, $participant) {
            foreach ($unreadMessages as $message) {
                $this->createReadReceipt($message, $user);
            }

            // Update participant last_read_at
            $participant->update([
                'last_read_at' => now(),
            ]);
        });
    }

    /**
     * Get read receipts for a message.
     */
    public function getReadReceipts(Message $message): \Illuminate\Database\Eloquent\Collection
    {
        return $message->readReceipts()->with('user')->get();
    }
}

