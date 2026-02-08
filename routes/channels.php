<?php

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

// User conversations list updates (private channel)
Broadcast::channel('user.{userId}.conversations', function ($user, $userId) {
    return $user->id === $userId;
});

// Group events & invites notifications (public to group members)
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return \App\Models\GroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists();
});

// Live stream chat (public read, requires auth)
Broadcast::channel('livestream.{streamId}', function ($user, $streamId) {
    return (bool) $user; // any authenticated user can listen
});
