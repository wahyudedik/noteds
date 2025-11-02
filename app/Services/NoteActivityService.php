<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteActivity;
use App\Models\User;

class NoteActivityService
{
    /**
     * Log an activity for a note.
     */
    public function log(Note $note, User $user, string $action, ?string $description = null, ?array $changes = null, ?array $metadata = null): NoteActivity
    {
        return NoteActivity::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description ?? $this->generateDescription($action, $note),
            'changes' => $changes,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log note creation.
     */
    public function logCreated(Note $note, User $user): NoteActivity
    {
        return $this->log($note, $user, 'created', "Note '{$note->title}' was created");
    }

    /**
     * Log note update with changes.
     */
    public function logUpdated(Note $note, User $user, array $oldData, array $newData): NoteActivity
    {
        $changes = [];
        foreach ($newData as $field => $newValue) {
            if (isset($oldData[$field]) && $oldData[$field] !== $newValue) {
                $changes[$field] = [
                    'old' => $oldData[$field],
                    'new' => $newValue,
                ];
            }
        }

        return $this->log(
            $note,
            $user,
            'updated',
            "Note '{$note->title}' was updated",
            !empty($changes) ? $changes : null
        );
    }

    /**
     * Log note publication.
     */
    public function logPublished(Note $note, User $user): NoteActivity
    {
        return $this->log($note, $user, 'published', "Note '{$note->title}' was published to marketplace");
    }

    /**
     * Log tag changes.
     */
    public function logTagged(Note $note, User $user, array $addedTags, array $removedTags = []): NoteActivity
    {
        $metadata = [];
        if (!empty($addedTags)) {
            $metadata['tags_added'] = $addedTags;
        }
        if (!empty($removedTags)) {
            $metadata['tags_removed'] = $removedTags;
        }

        // Determine action based on what happened
        $action = 'tagged';
        if (!empty($removedTags) && empty($addedTags)) {
            $action = 'untagged';
        } elseif (!empty($addedTags) && !empty($removedTags)) {
            $action = 'tagged'; // Both added and removed
        }

        return $this->log(
            $note,
            $user,
            $action,
            "Tags updated for note '{$note->title}'",
            null,
            $metadata
        );
    }

    /**
     * Generate default description based on action.
     */
    protected function generateDescription(string $action, Note $note): string
    {
        return match($action) {
            'created' => "Note '{$note->title}' was created",
            'updated' => "Note '{$note->title}' was updated",
            'published' => "Note '{$note->title}' was published",
            'unpublished' => "Note '{$note->title}' was unpublished",
            default => "Action '{$action}' performed on note '{$note->title}'",
        };
    }
}

