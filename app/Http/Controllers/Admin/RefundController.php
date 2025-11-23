<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\BuyerProtectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private BuyerProtectionService $buyerProtectionService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of refunds.
     */
    public function index(Request $request): View
    {
        $refunds = Refund::with(['buyer', 'seller', 'note', 'transaction'])
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('buyer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => Refund::where('status', 'pending')->count(),
            'approved' => Refund::where('status', 'approved')->count(),
            'rejected' => Refund::where('status', 'rejected')->count(),
            'processed' => Refund::where('status', 'processed')->count(),
            'total_amount_pending' => Refund::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Display the specified refund.
     */
    public function show(Refund $refund): View
    {
        $refund->load(['buyer', 'seller', 'note', 'transaction', 'processedBy']);

        return view('admin.refunds.show', compact('refund'));
    }

    /**
     * Approve a refund request.
     */
    public function approve(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== 'pending') {
            return redirect()->route('admin.refunds.show', $refund)
                ->with('error', 'Refund request is not pending.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check refund policy enforcement
        $policyCheck = $this->buyerProtectionService->enforceRefundPolicy($refund);
        if (!$policyCheck['compliant']) {
            // Show violations but allow admin to override
            $violations = $policyCheck['violations'];
            // Admin can still approve, but we log the violations
            \Log::warning('Refund approved despite policy violations', [
                'refund_id' => $refund->id,
                'violations' => $violations,
                'admin_id' => auth()->id(),
            ]);
        }

        // Use RefundService to process
        $refundService = app(\App\Services\RefundService::class);
        $refund->update([
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);
        $refundService->approveRefund($refund, auth()->user());

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Refund approved and processed successfully.');
    }

    /**
     * Reject a refund request.
     */
    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== 'pending') {
            return redirect()->route('admin.refunds.show', $refund)
                ->with('error', 'Refund request is not pending.');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $refund->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Notify buyer
        $this->notificationService->create(
            $refund->buyer,
            'refund_rejected',
            '❌ Refund Request Rejected',
            'Your refund request for ' . $refund->note->title . ' has been rejected. Reason: ' . $validated['admin_notes'],
            route('refunds.show', $refund),
            ['refund_id' => $refund->id]
        );

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Refund request rejected.');
    }
}
