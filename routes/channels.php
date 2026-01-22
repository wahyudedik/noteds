<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Default user private channel for notifications and other user-specific events
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});

// User-specific notifications channel
Broadcast::channel('user.{userId}.notifications', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    return $order && $order->user_id === $user->id;
});

// Stock price updates (public channel)
Broadcast::channel('stock.{stockCode}.prices', function ($user, $stockCode) {
    return true; // Public channel
});

// Stock signal updates (public channel)
Broadcast::channel('stock.{stockCode}.signals', function ($user, $stockCode) {
    return true; // Public channel
});

// User watchlist updates (private channel)
Broadcast::channel('user.{userId}.watchlist', function ($user, $userId) {
    return $user->id === $userId;
});

// Conversation channel - check if user is participant and not blocked
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }

    // Check if user is participant
    if (!$conversation->hasParticipant($user)) {
        return false;
    }

    // Check if user is blocked by any participant
    $otherParticipants = $conversation->activeParticipants()
        ->where('user_id', '!=', $user->id)
        ->get();

    foreach ($otherParticipants as $participant) {
        $otherUser = $participant->user;
        if ($user->hasBlocked($otherUser) || $otherUser->hasBlocked($user)) {
            return false;
        }
    }

    return true;
});

// User conversations list updates (private channel)
Broadcast::channel('user.{userId}.conversations', function ($user, $userId) {
    return $user->id === $userId;
});

// Group events & invites notifications (public to group members)
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return \App\Models\GroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();
});

