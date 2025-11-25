<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

// Activity Feed Channels
Broadcast::channel('activity-feed', function ($user) {
    return true; // Public channel for activity feed
});

Broadcast::channel('activity.{activityId}', function ($user, $activityId) {
    return true; // Public channel for specific activity updates
});

// Note Collaboration Channels
Broadcast::channel('note.collaboration.{noteId}', function ($user, $noteId) {
    $note = \App\Models\Note::find($noteId);
    
    if (!$note) {
        return false;
    }
    
    // Owner can always access
    if ($note->user_id === $user->id) {
        return true;
    }
    
    // Collaborators can access
    return $note->isCollaborator($user->id);
});

