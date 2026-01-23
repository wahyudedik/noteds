<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Events\MessageEdited;
use App\Events\MessageDeleted;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function __construct(
        private MessageMediaService $mediaService,
        private ReadReceiptService $readReceiptService,
        private NotificationService $notificationService
    ) {}

    /**
     * Send a message to a conversation.
     */
    public function sendMessage(Conversation $conversation, User $user, ?string $content = null, array $attachments = [], ?string $replyToId = null, array $metas = []): Message
    {
        // Check if user is participant
        if (!$conversation->hasParticipant($user)) {
            throw new \Exception('User is not a participant of this conversation.');
        }

        // Check if user is blocked
        $otherParticipants = $conversation->activeParticipants()
            ->where('user_id', '!=', $user->id)
            ->get();

        foreach ($otherParticipants as $participant) {
            $otherUser = $participant->user;
            if ($user->hasBlocked($otherUser) || $otherUser->hasBlocked($user)) {
                throw new \Exception('Cannot send message to blocked user.');
            }
        }

        // Determine message type
        $type = 'text';
        if (!empty($attachments)) {
            $firstAttachment = $attachments[0];
            $mimeType = $firstAttachment->getMimeType();
            
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mimeType, 'audio/')) {
                $type = 'voice';
            } else {
                $type = 'file';
            }
        }

        // Validate content (already validated in request, but double-check)
        if (empty($content) && empty($attachments)) {
            throw new \Exception('Message must have content or attachments.');
        }

        // Filter out null/empty attachments
        $attachments = array_filter($attachments, function ($file) {
            return $file !== null;
        });

        return DB::transaction(function () use ($conversation, $user, $content, $attachments, $replyToId, $type, $metas) {
            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $content,
                'type' => $type,
                'reply_to_id' => $replyToId,
            ]);

            // Handle attachments
            if (!empty($attachments)) {
                $this->mediaService->storeMedia($message, $attachments, $metas);
            }

            // Update conversation last_message_at
            $conversation->update([
                'last_message_at' => now(),
            ]);

            // Broadcast event
            broadcast(new MessageSent($message))->toOthers();

            // Send notifications
            $this->notificationService->notifyNewMessage($message);

            return $message->load(['user', 'media', 'replyTo']);
        });
    }

    /**
     * Send a voice message with metadata.
     */
    public function sendVoice(Conversation $conversation, User $user, UploadedFile $audio, int $duration, ?string $replyToId = null, array $meta = []): Message
    {
        if ($duration > 120) {
            throw new \Exception('Voice message exceeds maximum duration.');
        }
        return $this->sendMessage($conversation, $user, null, [$audio], $replyToId, [array_merge($meta, ['duration' => $duration])]);
    }

    /**
     * Edit a message.
     */
    public function editMessage(Message $message, User $user, string $newContent): Message
    {
        // Check if user is the sender
        if ($message->user_id !== $user->id) {
            throw new \Exception('Only the sender can edit the message.');
        }

        // Check if message is deleted
        if ($message->is_deleted) {
            throw new \Exception('Cannot edit deleted message.');
        }

        $message->update([
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        // Broadcast event
        broadcast(new MessageEdited($message))->toOthers();

        return $message->load(['user', 'media', 'replyTo']);
    }

    /**
     * Delete a message (soft delete).
     */
    public function deleteMessage(Message $message, User $user): void
    {
        // Check if user is the sender
        if ($message->user_id !== $user->id) {
            throw new \Exception('Only the sender can delete the message.');
        }

        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);

        // Broadcast event
        broadcast(new MessageDeleted($message))->toOthers();
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message, User $user): void
    {
        // Check if already read
        if ($message->isReadBy($user)) {
            return;
        }

        $this->readReceiptService->createReadReceipt($message, $user);
    }

    /**
     * Mark all messages in conversation as read.
     */
    public function markConversationAsRead(Conversation $conversation, User $user): void
    {
        $this->readReceiptService->markConversationAsRead($conversation, $user);
    }
}


