<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\EscrowLedger;
use App\Models\ServiceOrder;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Collection;

class OrderVerificationController extends Controller
{
    /**
     * Show pending order verifications list
     */
    public function pendingVerifications(Request $request): View
    {
        $query = ServiceOrder::whereAwaitingAdminVerification()
            ->with(['user', 'assignedVendor', 'workSubmissions'])
            ->latest('buyer_approved_at');

        // Filter by order ID
        if ($request->filled('order_id')) {
            $query->where('id', $request->input('order_id'));
        }

        // Filter by buyer name
        if ($request->filled('buyer_name')) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . request('buyer_name') . '%');
            });
        }

        // Filter by vendor name
        if ($request->filled('vendor_name')) {
            $query->whereHas('assignedVendor', function ($q) {
                $q->where('name', 'like', '%' . request('vendor_name') . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('buyer_approved_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('buyer_approved_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate(15);

        $stats = [
            'pending_count' => ServiceOrder::whereAwaitingAdminVerification()->count(),
            'total_escrow' => ServiceOrder::whereAwaitingAdminVerification()->sum('escrow_amount'),
            'verified_today' => ServiceOrder::where('admin_verified_at', '>=', now()->startOfDay())->count(),
        ];

        return view('admin.order-verification.index', compact('orders', 'stats'));
    }

    /**
     * Show order verification detail
     */
    public function show(ServiceOrder $order): View
    {
        // Verify admin can view this
        abort_unless($order->isAwaitingAdminVerification(), 403);

        // Calculate fee breakdown
        $escrowAmount = (float) $order->escrow_amount;
        $platformFeePercent = (float) settings('studio_platform_fee_percent') ?? 10;
        $platformFee = $escrowAmount * ($platformFeePercent / 100);
        $vendorNet = $escrowAmount - $platformFee;

        $breakdown = [
            'escrow_amount' => $escrowAmount,
            'platform_fee_percent' => $platformFeePercent,
            'platform_fee' => $platformFee,
            'vendor_net' => $vendorNet,
        ];

        $submission = $order->workSubmissions()->latest()->first();
        $approvalLogs = $order->approvalLogs()->latest('action_at')->get();

        return view('admin.order-verification.show', compact('order', 'breakdown', 'submission', 'approvalLogs'));
    }

    /**
     * Verify and release payment to vendor
     */
    public function verify(ServiceOrder $order, Request $request): RedirectResponse
    {
        // Verify admin can verify
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($order->isAwaitingAdminVerification(), 403);

        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string|min:10|max:1000',
        ]);

        try {
            // Begin transaction
            \DB::beginTransaction();

            // Calculate fee breakdown
            $escrowAmount = (float) $order->escrow_amount;
            $platformFeePercent = (float) settings('studio_platform_fee_percent') ?? 10;
            $platformFee = $escrowAmount * ($platformFeePercent / 100);
            $vendorNet = $escrowAmount - $platformFee;

            // Get wallets
            $adminWallet = Wallet::where('user_id', $this->getAdminWalletUserId())->firstOrFail();
            $vendorWallet = Wallet::where('user_id', $order->assigned_user_id)->firstOrFail();

            // Check admin has sufficient balance for fee (admin should already have it from escrow)
            // This is more of a validation - escrow is already held

            // Credit vendor wallet with net amount
            $vendorWallet->increment('balance', $vendorNet);

            // Record vendor payment in escrow ledger
            EscrowLedger::create([
                'service_order_id' => $order->id,
                'user_id' => $order->assigned_user_id,
                'type' => 'release',
                'amount' => $vendorNet,
                'milestone_index' => null,
                'meta' => [
                    'gross_amount' => $escrowAmount,
                    'platform_fee' => $platformFee,
                    'fee_percent' => $platformFeePercent,
                ],
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Credit admin wallet with fee
            $adminWallet->increment('balance', $platformFee);

            // Record admin fee in escrow ledger
            EscrowLedger::create([
                'service_order_id' => $order->id,
                'user_id' => $this->getAdminWalletUserId(),
                'type' => 'fee',
                'amount' => $platformFee,
                'milestone_index' => null,
                'meta' => [
                    'fee_percent' => $platformFeePercent,
                    'original_amount' => $escrowAmount,
                ],
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Update order status
            $order->update([
                'work_status' => 'verified', // Add this to enum migration if needed
                'admin_verified_by' => auth()->id(),
                'admin_verified_at' => now(),
                'admin_verification_notes' => $validated['notes'],
                'release_request_status' => 'approved',
                'escrow_amount' => 0, // Mark escrow as released
            ]);

            // Create approval log
            ApprovalLog::create([
                'service_order_id' => $order->id,
                'approver_id' => auth()->id(),
                'approver_type' => 'admin',
                'action' => 'payment_released',
                'notes' => $validated['notes'],
                'action_at' => now(),
            ]);

            // Create activity log
            $order->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'payment_released',
                'description' => 'Admin verified work and released payment to vendor',
                'meta' => [
                    'vendor_net' => $vendorNet,
                    'admin_fee' => $platformFee,
                    'admin_notes' => $validated['notes'],
                ],
            ]);

            // TODO: Send notifications
            // Notification::send($order->assignedVendor, new PaymentReleasedNotification($order, $vendorNet));
            // Notification::send(auth()->user(), new OrderVerifiedNotification($order));

            \DB::commit();

            return redirect()->route('admin.order-verification.index')
                ->with('success', "Payment released! Vendor received Rp " . number_format($vendorNet, 0, ',', '.'));
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Order verification failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to verify order: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject work and refund escrow to buyer
     */
    public function reject(ServiceOrder $order, Request $request): RedirectResponse
    {
        // Verify admin can reject
        abort_unless(auth()->user()->hasRole('admin'), 403);
        abort_unless($order->isAwaitingAdminVerification(), 403);

        // Validate request
        $validated = $request->validate([
            'notes' => 'required|string|min:10|max:1000',
        ]);

        try {
            \DB::beginTransaction();

            // Get buyer wallet
            $buyerWallet = Wallet::where('user_id', $order->user_id)->firstOrFail();
            $refundAmount = (float) $order->escrow_amount;

            // Refund to buyer
            $buyerWallet->increment('balance', $refundAmount);

            // Record refund in escrow ledger
            EscrowLedger::create([
                'service_order_id' => $order->id,
                'user_id' => $order->user_id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'milestone_index' => null,
                'meta' => [
                    'reason' => 'Admin rejected work quality',
                ],
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            // Update order status
            $order->update([
                'work_status' => 'rejected',
                'admin_verified_by' => auth()->id(),
                'admin_verified_at' => now(),
                'admin_verification_notes' => $validated['notes'],
                'release_request_status' => 'rejected',
                'escrow_amount' => 0,
            ]);

            // Create approval log
            ApprovalLog::create([
                'service_order_id' => $order->id,
                'approver_id' => auth()->id(),
                'approver_type' => 'admin',
                'action' => 'payment_rejected',
                'notes' => $validated['notes'],
                'action_at' => now(),
            ]);

            // Create activity log
            $order->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'payment_rejected',
                'description' => 'Admin rejected work and refunded escrow to buyer',
                'meta' => [
                    'refund_amount' => $refundAmount,
                    'rejection_reason' => $validated['notes'],
                ],
            ]);

            // TODO: Send notifications
            // Notification::send($order->user, new OrderRejectedNotification($order, $validated['notes']));
            // Notification::send($order->assignedVendor, new OrderRejectedNotification($order, $validated['notes']));

            \DB::commit();

            return redirect()->route('admin.order-verification.index')
                ->with('warning', "Work rejected! Refund of Rp " . number_format($refundAmount, 0, ',', '.') . " issued to buyer.");
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Order rejection failed: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to reject order: ' . $e->getMessage()]);
        }
    }

    /**
     * Get admin wallet user ID (configuration or hardcoded)
     */
    private function getAdminWalletUserId()
    {
        // TODO: This should be configurable
        // For now, assume first admin user or use a system account
        return \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->first()?->id;
    }
}
