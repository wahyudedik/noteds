<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\ServiceOrder;
use App\Models\WorkSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WorkSubmissionController extends Controller
{
    /**
     * Show work submission form
     */
    public function create(ServiceOrder $order): View
    {
        // Verify vendor is assigned to this order
        abort_unless($order->assigned_user_id === auth()->id(), 403);

        // Verify vendor can submit work
        abort_unless($order->canVendorSubmitWork(), 403);

        return view('studio.orders.work-submit', compact('order'));
    }

    /**
     * Store work submission
     */
    public function store(ServiceOrder $order, Request $request): RedirectResponse
    {
        // Verify vendor is assigned to this order
        abort_unless($order->assigned_user_id === auth()->id(), 403);

        // Verify vendor can submit work
        abort_unless($order->canVendorSubmitWork(), 403);

        // Validate request
        $validated = $request->validate([
            'description' => 'required|string|min:10|max:5000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:10240', // 10MB per file
        ]);

        // Check total file size doesn't exceed 50MB
        $totalSize = collect($request->file('files') ?? [])->sum(fn($file) => $file->getSize());
        if ($totalSize > 50 * 1024 * 1024) {
            return back()->withErrors(['files' => 'Total file size cannot exceed 50MB']);
        }

        // Store files
        $fileUrls = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('work-submissions', 'public');
                $fileUrls[] = '/storage/' . $path;
            }
        }

        // Create work submission
        $submission = WorkSubmission::create([
            'service_order_id' => $order->id,
            'vendor_id' => auth()->id(),
            'status' => 'submitted',
            'description' => $validated['description'],
            'files' => $fileUrls,
            'submitted_at' => now(),
        ]);

        // Update order status
        $order->update([
            'work_status' => 'submitted',
            'buyer_approval_status' => 'pending',
        ]);

        // Create approval log
        ApprovalLog::create([
            'service_order_id' => $order->id,
            'approver_id' => auth()->id(),
            'approver_type' => 'buyer', // Note: vendor submitting, but system logs this
            'action' => 'work_submitted',
            'notes' => 'Vendor submitted work',
            'action_at' => now(),
        ]);

        // Create activity log
        $order->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'work_submitted',
            'description' => 'Work has been submitted for review',
            'meta' => [
                'file_count' => count($fileUrls),
                'submission_id' => $submission->id,
            ],
        ]);

        // TODO: Send notification to buyer
        // Notification::send($order->user, new WorkSubmittedNotification($order, $submission));

        return redirect()->route('studio.orders.show', $order)
            ->with('success', 'Work submitted successfully! Waiting for buyer approval.');
    }

    /**
     * Show work submission detail
     */
    public function show(ServiceOrder $order): View
    {
        // Verify user is involved (buyer or vendor)
        abort_unless(
            $order->user_id === auth()->id() || $order->assigned_user_id === auth()->id(),
            403
        );

        $submission = $order->workSubmissions()->latest()->first();
        abort_unless($submission, 404);

        return view('studio.orders.work-detail', compact('order', 'submission'));
    }
}
