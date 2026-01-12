<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignCollaborator;
use App\Services\CampaignCollaborationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignCollaborationController extends Controller
{
    public function __construct(
        private CampaignCollaborationService $collaborationService
    ) {}

    /**
     * Invite a collaborator to a campaign.
     */
    public function invite(Request $request, Campaign $campaign)
    {
        if ($campaign->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:co_creator,manager,viewer',
            'can_edit' => 'boolean',
            'can_manage_budget' => 'boolean',
            'can_activate' => 'boolean',
        ]);

        try {
            $invitee = \App\Models\User::findOrFail($validated['user_id']);
            $collaboration = $this->collaborationService->inviteCollaborator(
                $campaign,
                auth()->user(),
                $invitee,
                $validated['role'],
                [
                    'can_edit' => $validated['can_edit'] ?? true,
                    'can_manage_budget' => $validated['can_manage_budget'] ?? false,
                    'can_activate' => $validated['can_activate'] ?? false,
                ]
            );

            // Send notification
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyCampaignCollaborationInvitation($collaboration);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send collaboration invitation notification', [
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'Collaboration invitation sent successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Accept a collaboration invitation.
     */
    public function accept(CampaignCollaborator $collaboration)
    {
        if ($collaboration->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->collaborationService->acceptInvitation($collaboration);
            
            // Send notification
            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notifyCampaignCollaborationAccepted($collaboration);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send collaboration accepted notification', [
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->with('success', 'Collaboration invitation accepted.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a collaboration invitation.
     */
    public function reject(CampaignCollaborator $collaboration)
    {
        if ($collaboration->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->collaborationService->rejectInvitation($collaboration);
            return back()->with('success', 'Collaboration invitation rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a collaborator from a campaign.
     */
    public function remove(Request $request, Campaign $campaign)
    {
        if ($campaign->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $collaborator = \App\Models\User::findOrFail($validated['user_id']);
            $this->collaborationService->removeCollaborator($campaign, $collaborator, auth()->user());
            return back()->with('success', 'Collaborator removed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update collaborator permissions.
     */
    public function updatePermissions(Request $request, CampaignCollaborator $collaboration)
    {
        if ($collaboration->campaign->creator_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'can_edit' => 'boolean',
            'can_manage_budget' => 'boolean',
            'can_activate' => 'boolean',
            'role' => 'in:co_creator,manager,viewer',
        ]);

        try {
            $this->collaborationService->updatePermissions($collaboration, $validated);
            return back()->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
