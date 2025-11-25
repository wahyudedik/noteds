<?php

namespace App\Http\Controllers;

use App\Events\CollaborationCommentAdded;
use App\Events\CollaboratorJoined;
use App\Events\CollaboratorLeft;
use App\Events\NoteContentUpdated;
use App\Models\Note;
use App\Models\NoteCollaborationComment;
use App\Models\NoteCollaborationSession;
use App\Models\NoteCollaborator;
use App\Models\NoteVersion;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteCollaborationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware('auth');
    }

    /**
     * Invite a collaborator to a note.
     */
    public function invite(Request $request, Note $note): RedirectResponse|JsonResponse
    {
        // Check if user can invite (owner or collaborator with invite permission)
        if ($note->user_id !== auth()->id()) {
            $collaborator = $note->collaborators()->where('user_id', auth()->id())->first();
            if (!$collaborator || !$collaborator->can_invite) {
                abort(403, 'You do not have permission to invite collaborators.');
            }
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:author,editor,viewer'],
            'can_edit' => ['boolean'],
            'can_delete' => ['boolean'],
            'can_invite' => ['boolean'],
        ]);

        // Prevent inviting yourself
        if ($validated['user_id'] === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot invite yourself.',
            ], 422);
        }

        // Check if user is already a collaborator
        if ($note->isCollaborator($validated['user_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a collaborator on this note.',
            ], 422);
        }

        $collaborator = NoteCollaborator::create([
            'note_id' => $note->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'can_edit' => $validated['can_edit'] ?? ($validated['role'] === 'editor' || $validated['role'] === 'author'),
            'can_delete' => $validated['can_delete'] ?? false,
            'can_invite' => $validated['can_invite'] ?? false,
            'invited_at' => now(),
        ]);

        // Notify the invited user
        $this->notificationService->create(
            $collaborator->user,
            'collaboration_invited',
            '👥 Collaboration Invitation',
            auth()->user()->name . ' invited you to collaborate on: ' . $note->title,
            route('notes.edit', $note),
            ['note_id' => $note->id, 'collaborator_id' => $collaborator->id]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collaborator' => $collaborator->load('user'),
                'message' => 'Collaborator invited successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Collaborator invited successfully.');
    }

    /**
     * Remove a collaborator from a note.
     */
    public function removeCollaborator(Note $note, NoteCollaborator $collaborator): RedirectResponse|JsonResponse
    {
        // Only owner or admin can remove collaborators
        if ($note->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $collaborator->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Collaborator removed successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Collaborator removed successfully.');
    }

    /**
     * Update collaborator permissions.
     */
    public function updateCollaborator(Request $request, Note $note, NoteCollaborator $collaborator): RedirectResponse|JsonResponse
    {
        // Only owner can update permissions
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'role' => ['sometimes', 'in:author,editor,viewer'],
            'can_edit' => ['boolean'],
            'can_delete' => ['boolean'],
            'can_invite' => ['boolean'],
        ]);

        $collaborator->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collaborator' => $collaborator->load('user'),
                'message' => 'Collaborator permissions updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Collaborator permissions updated successfully.');
    }

    /**
     * Join collaboration session.
     */
    public function joinSession(Request $request, Note $note): JsonResponse
    {
        // Check if user can edit
        if (!$note->canUserEdit(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this note.',
            ], 403);
        }

        // End any existing active session for this user on this note
        NoteCollaborationSession::where('note_id', $note->id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->update(['is_active' => false, 'left_at' => now()]);

        // Create new session
        $session = NoteCollaborationSession::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'joined_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);

        // Broadcast event
        event(new CollaboratorJoined($note, auth()->user()));

        return response()->json([
            'success' => true,
            'session' => $session,
            'message' => 'Joined collaboration session.',
        ]);
    }

    /**
     * Leave collaboration session.
     */
    public function leaveSession(Note $note): JsonResponse
    {
        $session = NoteCollaborationSession::where('note_id', $note->id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if ($session) {
            $session->leave();
            event(new CollaboratorLeft($note, auth()->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Left collaboration session.',
        ]);
    }

    /**
     * Update session activity (cursor position, etc.).
     */
    public function updateSessionActivity(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'cursor_position' => ['nullable', 'string'],
            'selection_range' => ['nullable', 'array'],
        ]);

        $session = NoteCollaborationSession::where('note_id', $note->id)
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if ($session) {
            $session->update([
                'cursor_position' => $validated['cursor_position'] ?? null,
                'selection_range' => $validated['selection_range'] ?? null,
                'last_activity_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get active collaborators for a note.
     */
    public function getActiveCollaborators(Note $note): JsonResponse
    {
        $sessions = NoteCollaborationSession::where('note_id', $note->id)
            ->where('is_active', true)
            ->where('last_activity_at', '>', now()->subMinutes(5))
            ->with('user:id,name,username,avatar')
            ->get();

        return response()->json([
            'success' => true,
            'collaborators' => $sessions->map(function ($session) {
                return [
                    'user' => $session->user,
                    'cursor_position' => $session->cursor_position,
                    'joined_at' => $session->joined_at,
                ];
            }),
        ]);
    }

    /**
     * Save a new version of the note.
     */
    public function saveVersion(Request $request, Note $note): JsonResponse
    {
        // Check if user can edit
        if (!$note->canUserEdit(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this note.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string'],
            'change_description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($note, $validated) {
            // Mark all previous versions as not current
            NoteVersion::where('note_id', $note->id)->update(['is_current' => false]);

            // Create new version
            $version = NoteVersion::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'version_number' => NoteVersion::getNextVersionNumber($note->id),
                'title' => $validated['title'],
                'content' => $validated['content'],
                'summary' => $validated['summary'] ?? null,
                'metadata' => [
                    'price' => $note->price,
                    'tags' => $note->tags->pluck('name')->toArray(),
                ],
                'change_description' => $validated['change_description'] ?? null,
                'is_current' => true,
            ]);

            // Update note content
            $note->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]);

            // Broadcast update
            event(new NoteContentUpdated($note, auth()->user(), [
                'version_number' => $version->version_number,
                'change_description' => $validated['change_description'] ?? null,
            ]));
        });

        return response()->json([
            'success' => true,
            'message' => 'Version saved successfully.',
        ]);
    }

    /**
     * Get all versions for a note.
     */
    public function getVersions(Note $note): JsonResponse
    {
        $versions = $note->versions()
            ->with('user:id,name,username')
            ->orderBy('version_number', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'versions' => $versions,
        ]);
    }

    /**
     * Restore a specific version.
     */
    public function restoreVersion(Note $note, NoteVersion $version): JsonResponse
    {
        // Check if user can edit
        if (!$note->canUserEdit(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this note.',
            ], 403);
        }

        // Verify version belongs to note
        if ($version->note_id !== $note->id) {
            abort(404);
        }

        DB::transaction(function () use ($note, $version) {
            // Mark all versions as not current
            NoteVersion::where('note_id', $note->id)->update(['is_current' => false]);

            // Create new version from restored content
            $newVersion = NoteVersion::create([
                'note_id' => $note->id,
                'user_id' => auth()->id(),
                'version_number' => NoteVersion::getNextVersionNumber($note->id),
                'title' => $version->title,
                'content' => $version->content,
                'summary' => $version->summary,
                'metadata' => $version->metadata,
                'change_description' => 'Restored from version ' . $version->version_number,
                'is_current' => true,
            ]);

            // Restore note content
            $note->update([
                'title' => $version->title,
                'content' => $version->content,
            ]);

            // Broadcast update
            event(new NoteContentUpdated($note, auth()->user(), [
                'version_number' => $newVersion->version_number,
                'change_description' => 'Restored from version ' . $version->version_number,
            ]));
        });

        return response()->json([
            'success' => true,
            'message' => 'Version restored successfully.',
        ]);
    }

    /**
     * Add a collaboration comment.
     */
    public function addComment(Request $request, Note $note): JsonResponse
    {
        // Check if user is collaborator or owner
        if ($note->user_id !== auth()->id() && !$note->isCollaborator(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to comment on this note.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'parent_id' => ['nullable', 'exists:note_collaboration_comments,id'],
            'target_type' => ['nullable', 'in:line,section,general'],
            'target_reference' => ['nullable', 'string'],
            'target_position' => ['nullable', 'array'],
        ]);

        $comment = NoteCollaborationComment::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'target_type' => $validated['target_type'] ?? 'general',
            'target_reference' => $validated['target_reference'] ?? null,
            'target_position' => $validated['target_position'] ?? null,
        ]);

        // Broadcast event
        event(new CollaborationCommentAdded($note, $comment));

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user', 'parent'),
            'message' => 'Comment added successfully.',
        ]);
    }

    /**
     * Get collaboration comments for a note.
     */
    public function getComments(Note $note): JsonResponse
    {
        $comments = $note->collaborationComments()
            ->with(['user:id,name,username,avatar', 'replies.user:id,name,username,avatar'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Resolve a collaboration comment.
     */
    public function resolveComment(Note $note, NoteCollaborationComment $comment): JsonResponse
    {
        // Verify comment belongs to note
        if ($comment->note_id !== $note->id) {
            abort(404);
        }

        // Check if user can resolve (owner, commenter, or collaborator with edit permission)
        if ($note->user_id !== auth()->id() 
            && $comment->user_id !== auth()->id() 
            && !$note->canUserEdit(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to resolve this comment.',
            ], 403);
        }

        $comment->resolve();

        return response()->json([
            'success' => true,
            'message' => 'Comment resolved successfully.',
        ]);
    }

    /**
     * Get collaborators list for a note.
     */
    public function getCollaborators(Note $note): JsonResponse
    {
        $collaborators = $note->collaborators()
            ->with('user:id,name,username,avatar,email')
            ->get();

        return response()->json([
            'success' => true,
            'collaborators' => $collaborators,
            'owner' => $note->user->only(['id', 'name', 'username', 'avatar']),
        ]);
    }
}
