<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;

/**
 * Message Authorization Policy
 * 
 * Security Controls:
 * - Only participants can view messages
 * - Message sender controls editing/deletion
 * - Rate limiting on message creation
 * - Encryption of sensitive message content
 * - Audit logging of all message operations
 */
class MessagePolicy extends BasePolicy
{
    /**
     * User can view their messages
     */
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id ||
            $user->id === $message->recipient_id;
    }

    /**
     * User can create messages if active and not rate limited
     */
    public function create(User $user): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        if (!$this->isActive($user)) {
            return false;
        }

        if ($this->isSuspended($user)) {
            return false;
        }

        // Rate limiting: max 100 messages per hour
        $recentMessages = $user->sentMessages()
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentMessages >= 100) {
            return false;
        }

        $this->logAccess($user, 'create', 'Message');
        return true;
    }

    /**
     * Only sender can edit their own messages
     */
    public function update(User $user, Message $message): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Only sender can edit
        if ($user->id !== $message->sender_id) {
            return false;
        }

        // Cannot edit read messages
        if ($message->read_at) {
            return false;
        }

        $this->logAccess($user, 'update', 'Message', ['message_id' => $message->id]);
        return true;
    }

    /**
     * Only sender can delete their own messages
     */
    public function delete(User $user, Message $message): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        if ($user->id !== $message->sender_id) {
            return false;
        }

        $this->logAccess($user, 'delete', 'Message', ['message_id' => $message->id]);
        return true;
    }

    /**
     * Recipient can mark message as read
     */
    public function markAsRead(User $user, Message $message): bool
    {
        if ($user->id !== $message->recipient_id) {
            return false;
        }

        return true;
    }

    /**
     * User can report abusive message
     */
    public function report(User $user, Message $message): bool
    {
        if (!$this->isAuthenticated($user)) {
            return false;
        }

        // Can only report messages they received
        if ($user->id !== $message->recipient_id) {
            return false;
        }

        // Can't report own messages
        if ($user->id === $message->sender_id) {
            return false;
        }

        $this->logAccess($user, 'report', 'Message', ['message_id' => $message->id]);
        return true;
    }
}
