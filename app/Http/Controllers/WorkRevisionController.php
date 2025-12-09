<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\WorkRevision;
use App\Notifications\RevisionRequestedNotification;
use App\Notifications\RevisionSubmittedNotification;
use App\Notifications\RevisionRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkRevisionController extends Controller
{
    /**
     * Request a revision from vendor
     * POST /work/{order}/request-revision
     */
    public function requestRevision(Request $request, ServiceOrder $order)
    {
        // Authorization check
        if (!$order->canBuyerRequestRevision(Auth::user())) {
            return back()->withErrors('You cannot request a revision for this order.');
        }

        // Validate input
        $validated = $request->validate([
            'request_reason' => 'required|string|max:1000',
        ]);

        // Check if order already has a pending revision
        if ($order->getCurrentPendingRevision()) {
            return back()->withErrors('There is already a pending revision request for this order.');
        }

        // Create revision record
        $revision = $order->workRevisions()->create([
            'revision_number' => $order->revision_count + 1,
            'requested_by' => Auth::id(),
            'request_reason' => $validated['request_reason'],
            'status' => 'pending',
        ]);

        // Update order status
        $order->update([
            'revision_count' => $order->revision_count + 1,
            'current_revision_number' => $order->revision_count + 1,
            'revision_status' => 'requested',
            'work_status' => 'pending', // Reset to pending until vendor submits
            'buyer_approval_status' => 'pending',
        ]);

        // Log in approval log
        $order->approvalLogs()->create([
            'approver_id' => Auth::id(),
            'action' => 'revision_requested',
            'notes' => $validated['request_reason'],
        ]);

        // Send notification to vendor
        $order->assignedVendor->notify(new RevisionRequestedNotification($order, $revision));

        return back()->with('success', 'Revision request sent to vendor.');
    }

    /**
     * Submit revised work
     * POST /revisions/{revision}/submit
     */
    public function submitRevision(Request $request, WorkRevision $revision)
    {
        $order = $revision->serviceOrder;

        // Authorization check
        if (!$order->canVendorSubmitRevision(Auth::user())) {
            return back()->withErrors('You cannot submit a revision for this order.');
        }

        // Validate input
        $validated = $request->validate([
            'submission_notes' => 'nullable|string|max:1000',
        ]);

        // Check if revision is still pending
        if (!$revision->isPending()) {
            return back()->withErrors('This revision request is no longer pending.');
        }

        // Update revision
        $revision->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
            'submission_notes' => $validated['submission_notes'] ?? null,
        ]);

        // Update order status
        $order->update([
            'revision_status' => 'submitted',
            'work_status' => 'submitted',
            'buyer_approval_status' => 'pending',
        ]);

        // Log in approval log
        $order->approvalLogs()->create([
            'approver_id' => Auth::id(),
            'action' => 'revision_submitted',
            'notes' => "Revision #{$revision->revision_number} submitted" . ($validated['submission_notes'] ? ': ' . $validated['submission_notes'] : ''),
        ]);

        // Send notification to buyer
        $order->user->notify(new RevisionSubmittedNotification($order, $revision));

        return back()->with('success', 'Revised work submitted for approval.');
    }

    /**
     * Accept/Approve submitted revision
     * POST /revisions/{revision}/approve
     */
    public function approveRevision(Request $request, WorkRevision $revision)
    {
        $order = $revision->serviceOrder;

        // Authorization check - only buyer can approve
        if ($order->user_id !== Auth::id()) {
            return back()->withErrors('You cannot approve revisions for this order.');
        }

        // Check if revision is submitted
        if (!$revision->isSubmitted()) {
            return back()->withErrors('This revision has not been submitted yet.');
        }

        // Update revision
        $revision->update([
            'status' => 'accepted',
        ]);

        // Update order status
        $order->update([
            'revision_status' => 'none',
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
            'buyer_approved_at' => now(),
            'buyer_approval_notes' => 'Revision #' . $revision->revision_number . ' approved',
        ]);

        // Log in approval log
        $order->approvalLogs()->create([
            'approver_id' => Auth::id(),
            'action' => 'revision_accepted',
            'notes' => "Revision #{$revision->revision_number} accepted by buyer",
        ]);

        return back()->with('success', 'Revision approved. Awaiting admin verification for payment release.');
    }

    /**
     * Reject submitted revision
     * POST /revisions/{revision}/reject
     */
    public function rejectRevision(Request $request, WorkRevision $revision)
    {
        $order = $revision->serviceOrder;

        // Authorization check - only buyer can reject
        if ($order->user_id !== Auth::id()) {
            return back()->withErrors('You cannot reject revisions for this order.');
        }

        // Validate input
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Check if revision is submitted
        if (!$revision->isSubmitted()) {
            return back()->withErrors('This revision has not been submitted yet.');
        }

        // Check remaining revisions
        if ($order->getRemainingRevisions() <= 0) {
            return back()->withErrors('No more revisions allowed. Please contact admin for dispute resolution.');
        }

        // Update revision
        $revision->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Update order - reset to pending for next revision request
        $order->update([
            'revision_status' => 'none',
            'work_status' => 'pending',
            'buyer_approval_status' => 'pending',
        ]);

        // Log in approval log
        $order->approvalLogs()->create([
            'approver_id' => Auth::id(),
            'action' => 'revision_rejected',
            'notes' => $validated['rejection_reason'],
        ]);

        // Send notification to vendor
        $order->assignedVendor->notify(new RevisionRejectedNotification($order, $revision));

        $remaining = $order->getRemainingRevisions();
        return back()->with('success', "Revision rejected. Vendor has {$remaining} revision(s) remaining.");
    }

    /**
     * View revision history for an order
     * GET /orders/{order}/revision-history
     */
    public function viewHistory(ServiceOrder $order)
    {
        // Authorization - buyer or vendor
        if ($order->user_id !== Auth::id() && $order->assigned_user_id !== Auth::id()) {
            abort(403);
        }

        $revisions = $order->workRevisions()->latest('created_at')->get();
        
        return view('studio.orders.revision-history', compact('order', 'revisions'));
    }
}
