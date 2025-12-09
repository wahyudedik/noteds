<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\WorkSubmission;
use App\Notifications\WorkApprovedNotification;
use App\Notifications\WorkRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class BuyerApprovalController extends Controller
{
    /**
     * Approve work submission
     */
    public function approve(ServiceOrder $order, Request $request): RedirectResponse
    {
        // Verify user is the buyer
        abort_unless($order->user_id === auth()->id(), 403);

        // Verify order can be approved
        abort_unless($order->canBuyerApprove(), 403);

        // Validate request
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get latest work submission
        $submission = $order->workSubmissions()->latest()->firstOrFail();

        // Update submission status
        $submission->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Update order status
        $order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
            'buyer_approved_at' => now(),
            'buyer_approval_notes' => $validated['notes'] ?? null,
        ]);

        // Create approval log
        ApprovalLog::create([
            'service_order_id' => $order->id,
            'approver_id' => auth()->id(),
            'approver_type' => 'buyer',
            'action' => 'work_approved',
            'notes' => $validated['notes'] ?? 'Buyer approved the work',
            'action_at' => now(),
        ]);

        // Create activity log
        $order->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'work_approved',
            'description' => 'Buyer approved the submitted work',
            'meta' => [
                'approver_notes' => $validated['notes'] ?? null,
            ],
        ]);

        // Send notification to vendor
        if ($order->assignedVendor) {
            Notification::send($order->assignedVendor, new WorkApprovedNotification($order));
        }

        // Send notification to admins
        $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new WorkApprovedNotification($order));
        }

        return redirect()->route('studio.orders.show', $order)
            ->with('success', 'Work approved! Waiting for admin verification and payment release.');
    }

    /**
     * Reject work submission
     */
    public function reject(ServiceOrder $order, Request $request): RedirectResponse
    {
        // Verify user is the buyer
        abort_unless($order->user_id === auth()->id(), 403);

        // Verify order can be rejected
        abort_unless($order->canBuyerApprove(), 403);

        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string|min:10|max:1000',
        ]);

        // Get latest work submission
        $submission = $order->workSubmissions()->latest()->firstOrFail();

        // Update submission status
        $submission->update([
            'status' => 'rejected',
        ]);

        // Update order status
        $order->update([
            'work_status' => 'rejected',
            'buyer_approval_status' => 'rejected',
            'buyer_approval_notes' => $validated['notes'],
        ]);

        // Create approval log
        ApprovalLog::create([
            'service_order_id' => $order->id,
            'approver_id' => auth()->id(),
            'approver_type' => 'buyer',
            'action' => 'work_rejected',
            'notes' => $validated['notes'],
            'action_at' => now(),
        ]);

        // Create activity log
        $order->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'work_rejected',
            'description' => 'Buyer rejected the work and requested revision',
            'meta' => [
                'rejection_reason' => $validated['notes'],
            ],
        ]);

        // Send notification to vendor
        if ($order->assignedVendor) {
            Notification::send($order->assignedVendor, new WorkRejectedNotification($order, $validated['notes']));
        }

        return redirect()->route('studio.orders.show', $order)
            ->with('warning', 'Work rejected. Vendor will be notified to revise.');
    }
}
