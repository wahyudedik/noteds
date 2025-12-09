<?php

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WorkSubmission;
use App\Models\ApprovalLog;

/**
 * Unit Tests for ServiceOrder Payment Verification Methods
 */

describe('ServiceOrder Permission Methods', function () {

    beforeEach(function () {
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->otherVendor = User::factory()->create(['role' => 'seller']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        Wallet::create(['user_id' => $this->buyer->id, 'balance' => 5000000, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Service',
            'description' => 'Service Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('allows assigned vendor to submit work', function () {
        expect($this->order->canVendorSubmitWork($this->vendor))->toBeTrue();
    });

    it('prevents non-assigned vendor from submitting work', function () {
        expect($this->order->canVendorSubmitWork($this->otherVendor))->toBeFalse();
    });

    it('prevents buyer from submitting work', function () {
        expect($this->order->canVendorSubmitWork($this->buyer))->toBeFalse();
    });

    it('prevents vendor from submitting work twice', function () {
        // First submission
        $this->order->update(['work_status' => 'submitted']);
        expect($this->order->canVendorSubmitWork($this->vendor))->toBeFalse();
    });

    it('allows buyer to approve work when work is submitted', function () {
        $this->order->update(['work_status' => 'submitted']);
        expect($this->order->canBuyerApprove($this->buyer))->toBeTrue();
    });

    it('prevents non-buyer from approving work', function () {
        $this->order->update(['work_status' => 'submitted']);
        expect($this->order->canBuyerApprove($this->vendor))->toBeFalse();
        expect($this->order->canBuyerApprove($this->otherVendor))->toBeFalse();
    });

    it('prevents approval when work not submitted', function () {
        expect($this->order->canBuyerApprove($this->buyer))->toBeFalse();
    });

    it('allows admin to verify work when buyer approved', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
        ]);
        expect($this->order->canAdminVerify($this->admin))->toBeTrue();
    });

    it('prevents non-admin from verifying work', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
        ]);
        expect($this->order->canAdminVerify($this->buyer))->toBeFalse();
        expect($this->order->canAdminVerify($this->vendor))->toBeFalse();
    });

    it('prevents verification when buyer did not approve', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'pending',
        ]);
        expect($this->order->canAdminVerify($this->admin))->toBeFalse();
    });
});

/**
 * Unit Tests for ServiceOrder Status Check Methods
 */
describe('ServiceOrder Status Check Methods', function () {

    beforeEach(function () {
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Service',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('detects pending work submission correctly', function () {
        $this->order->update(['work_status' => 'submitted']);
        expect($this->order->hasPendingWorkSubmission())->toBeTrue();
    });

    it('detects no pending work submission', function () {
        $this->order->update(['work_status' => 'not_submitted']);
        expect($this->order->hasPendingWorkSubmission())->toBeFalse();
    });

    it('detects when order awaits admin verification', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
        ]);
        expect($this->order->isAwaitingAdminVerification())->toBeTrue();
    });

    it('detects when order does not await admin verification', function () {
        $this->order->update([
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
        expect($this->order->isAwaitingAdminVerification())->toBeFalse();
    });

    it('detects fully verified orders', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
            'admin_verified_at' => now(),
        ]);
        expect($this->order->isFullyVerified())->toBeTrue();
    });

    it('detects non-verified orders', function () {
        $this->order->update([
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
            'admin_verified_at' => null,
        ]);
        expect($this->order->isFullyVerified())->toBeFalse();
    });
});

/**
 * Unit Tests for WorkSubmission Model
 */
describe('WorkSubmission Model', function () {

    beforeEach(function () {
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Service',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
        ]);

        $this->submission = WorkSubmission::create([
            'service_order_id' => $this->order->id,
            'vendor_id' => $this->vendor->id,
            'status' => 'submitted',
            'description' => 'Work description',
            'files' => ['file1.pdf', 'file2.pdf'],
        ]);
    });

    it('correctly counts files in submission', function () {
        expect($this->submission->getFileCount())->toBe(2);
    });

    it('detects approved status correctly', function () {
        expect($this->submission->isApprovedByBuyer())->toBeFalse();

        $this->submission->update(['status' => 'approved']);
        expect($this->submission->isApprovedByBuyer())->toBeTrue();
    });

    it('detects rejected status correctly', function () {
        expect($this->submission->isRejected())->toBeFalse();

        $this->submission->update(['status' => 'rejected']);
        expect($this->submission->isRejected())->toBeTrue();
    });

    it('generates correct status label', function () {
        expect($this->submission->getStatusLabel())->toBe('Submitted');

        $this->submission->update(['status' => 'approved']);
        expect($this->submission->getStatusLabel())->toBe('Approved');

        $this->submission->update(['status' => 'rejected']);
        expect($this->submission->getStatusLabel())->toBe('Rejected');
    });
});

/**
 * Unit Tests for ApprovalLog Model
 */
describe('ApprovalLog Model', function () {

    beforeEach(function () {
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'title' => 'Service',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
        ]);

        $this->buyerLog = ApprovalLog::create([
            'service_order_id' => $this->order->id,
            'approver_id' => $this->buyer->id,
            'approver_type' => 'buyer',
            'action' => 'work_approved',
            'notes' => 'Looks great!',
            'action_at' => now(),
        ]);

        $this->adminLog = ApprovalLog::create([
            'service_order_id' => $this->order->id,
            'approver_id' => $this->admin->id,
            'approver_type' => 'admin',
            'action' => 'payment_released',
            'notes' => 'Verified and released',
            'action_at' => now(),
        ]);
    });

    it('generates correct approver type label for buyer', function () {
        expect($this->buyerLog->getApproverTypeLabel())->toBe('Buyer');
    });

    it('generates correct approver type label for admin', function () {
        expect($this->adminLog->getApproverTypeLabel())->toBe('Admin');
    });

    it('generates correct action label', function () {
        expect($this->buyerLog->getActionLabel())->toBe('Work Approved');
        expect($this->adminLog->getActionLabel())->toBe('Payment Released');
    });

    it('generates timeline display correctly', function () {
        $timeline = $this->buyerLog->getTimelineDisplay();

        expect($timeline)->toContain('Work Approved');
        expect($timeline)->toContain('Buyer');
    });
});

/**
 * Unit Tests for ServiceOrder Query Scopes
 */
describe('ServiceOrder Query Scopes', function () {

    beforeEach(function () {
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);

        // Create orders with different statuses
        $this->approvedOrder = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Approved Order',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'approved',
            'buyer_approval_status' => 'approved',
        ]);

        $this->pendingOrder = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Pending Order',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'submitted',
            'buyer_approval_status' => 'pending',
        ]);

        $this->rejectedOrder = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Rejected Order',
            'description' => 'Description',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'rejected',
            'buyer_approval_status' => 'rejected',
        ]);
    });

    it('filters work approved orders correctly', function () {
        $approved = ServiceOrder::whereWorkApproved()->get();

        expect($approved->count())->toBe(1);
        expect($approved->first()->id)->toBe($this->approvedOrder->id);
    });

    it('filters awaiting admin verification orders correctly', function () {
        $awaitingVerification = ServiceOrder::whereAwaitingAdminVerification()->get();

        expect($awaitingVerification->count())->toBe(1);
        expect($awaitingVerification->first()->id)->toBe($this->approvedOrder->id);
    });

    it('filters work rejected orders correctly', function () {
        $rejected = ServiceOrder::whereWorkRejected()->get();

        expect($rejected->count())->toBe(1);
        expect($rejected->first()->id)->toBe($this->rejectedOrder->id);
    });

    it('filters pending work submission orders correctly', function () {
        $pending = ServiceOrder::wherePendingWorkSubmission()->get();

        expect($pending->count())->toBe(1);
        expect($pending->first()->id)->toBe($this->pendingOrder->id);
    });
});
