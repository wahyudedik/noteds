<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCollaborator;
use App\Models\User;
use App\Services\PostCollaborationService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCollaborationController extends Controller
{
    public function __construct(
        private PostCollaborationService $collaborationService,
        private NotificationService $notificationService
    ) {}

    /**
     * Invite a collaborator to a post.
     */
    public function invite(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        // Only post owner can invite collaborators
        if ($post->user_id !== $request->user()->id) {
            abort(403, 'Only the post owner can invite collaborators.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'in:co_author,editor,contributor'],
            'can_edit' => ['nullable', 'boolean'],
            'can_publish' => ['nullable', 'boolean'],
        ]);

        try {
            $invitee = User::findOrFail($validated['user_id']);
            $role = $validated['role'] ?? 'co_author';
            $permissions = [
                'can_edit' => $validated['can_edit'] ?? true,
                'can_publish' => $validated['can_publish'] ?? false,
            ];

            $collaboration = $this->collaborationService->inviteCollaborator(
                $post,
                $request->user(),
                $invitee,
                $role,
                $permissions
            );

            // Send notification
            $this->notificationService->notifyCollaborationInvitation($collaboration);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Collaboration invitation sent successfully.',
                    'collaboration' => $collaboration->load('user'),
                ]);
            }

            return back()->with('success', 'Collaboration invitation sent successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Accept a collaboration invitation.
     */
    public function accept(Request $request, PostCollaborator $collaboration): JsonResponse|RedirectResponse
    {
        // Only the invited user can accept
        if ($collaboration->user_id !== $request->user()->id) {
            abort(403, 'You can only accept your own invitations.');
        }

        try {
            $this->collaborationService->acceptInvitation($collaboration);

            // Send notification
            $this->notificationService->notifyCollaborationAccepted($collaboration);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Collaboration invitation accepted.',
                ]);
            }

            return back()->with('success', 'Collaboration invitation accepted.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a collaboration invitation.
     */
    public function reject(Request $request, PostCollaborator $collaboration): JsonResponse|RedirectResponse
    {
        // Only the invited user can reject
        if ($collaboration->user_id !== $request->user()->id) {
            abort(403, 'You can only reject your own invitations.');
        }

        try {
            $this->collaborationService->rejectInvitation($collaboration);

            // Send notification
            $this->notificationService->notifyCollaborationRejected($collaboration);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Collaboration invitation rejected.',
                ]);
            }

            return back()->with('success', 'Collaboration invitation rejected.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a collaborator from a post.
     */
    public function remove(Request $request, Post $post, User $collaborator): JsonResponse|RedirectResponse
    {
        try {
            $this->collaborationService->removeCollaborator($post, $collaborator, $request->user());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Collaborator removed successfully.',
                ]);
            }

            return back()->with('success', 'Collaborator removed successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update permissions for a collaborator.
     */
    public function updatePermissions(Request $request, PostCollaborator $collaboration): JsonResponse|RedirectResponse
    {
        // Only post owner can update permissions
        if ($collaboration->post->user_id !== $request->user()->id) {
            abort(403, 'Only the post owner can update collaborator permissions.');
        }

        $validated = $request->validate([
            'can_edit' => ['nullable', 'boolean'],
            'can_publish' => ['nullable', 'boolean'],
            'role' => ['nullable', 'in:co_author,editor,contributor'],
        ]);

        try {
            $this->collaborationService->updatePermissions($collaboration, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissions updated successfully.',
                    'collaboration' => $collaboration->fresh()->load('user'),
                ]);
            }

            return back()->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
