<?php

use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WorkSubmission;
use App\Models\ApprovalLog;
use App\Models\EscrowLedger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Studio Payment Verification System - Complete Flow Tests
 * 
 * Tests the entire payment verification workflow:
 * 1. Buyer funds escrow
 * 2. Vendor submits work
 * 3. Buyer approves work
 * 4. Admin verifies and releases payment
 */

describe('Studio Payment Verification - Complete Flow', function () {

    beforeEach(function () {
        Storage::fake('public');

        // Create test users
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create wallets
        Wallet::create(['user_id' => $this->buyer->id, 'balance' => 5000000, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->admin->id, 'balance' => 0, 'currency' => 'IDR']);

        // Create service order
        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Design Website',
            'description' => 'Design a professional website',
            'budget' => 500000,
            'status' => 'quoted',
            'escrow_amount' => 0,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('completes the full payment verification flow', function () {
        // Step 1: Buyer funds escrow
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 500000])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->escrow_amount)->toBe(500000.00);
        expect($this->order->status)->toBe('in_progress');

        // Verify buyer wallet decreased
        $buyerWallet = Wallet::where('user_id', $this->buyer->id)->first();
        expect($buyerWallet->balance)->toBe(4500000.00);

        // Verify escrow ledger created
        $fundLog = EscrowLedger::where('service_order_id', $this->order->id)
            ->where('type', 'fund')
            ->first();
        expect($fundLog)->not->toBeNull();
        expect($fundLog->amount)->toBe(500000.00);

        // Step 2: Vendor submits work
        $files = [
            UploadedFile::fake()->create('design.pdf', 500),
            UploadedFile::fake()->create('mockup.figma', 1000),
        ];

        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'I have completed the website design with modern UI/UX principles and responsive layouts.',
                'files' => $files,
            ])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->work_status)->toBe('submitted');

        // Verify work submission created
        $submission = WorkSubmission::where('service_order_id', $this->order->id)->first();
        expect($submission)->not->toBeNull();
        expect($submission->vendor_id)->toBe($this->vendor->id);
        expect($submission->status)->toBe('submitted');
        expect($submission->getFileCount())->toBe(2);

        // Step 3: Buyer approves work
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order), [
                'notes' => 'Excellent design work, exactly what I wanted!',
            ])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->work_status)->toBe('approved');
        expect($this->order->buyer_approval_status)->toBe('approved');
        expect($this->order->buyer_approved_at)->not->toBeNull();

        // Verify submission updated
        $submission->refresh();
        expect($submission->status)->toBe('approved');
        expect($submission->approved_by)->toBe($this->buyer->id);

        // Verify approval log created
        $approvalLog = ApprovalLog::where('service_order_id', $this->order->id)
            ->where('action', 'work_approved')
            ->first();
        expect($approvalLog)->not->toBeNull();
        expect($approvalLog->approver_type)->toBe('buyer');

        // Step 4: Admin verifies and releases payment
        $this->actingAs($this->admin)
            ->post(route('admin.order-verification.verify', $this->order), [
                'notes' => 'Work meets all quality standards. Verified and approved.',
            ])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->admin_verified_by)->toBe($this->admin->id);
        expect($this->order->admin_verified_at)->not->toBeNull();
        expect($this->order->status)->toBe('completed');

        // Verify vendor receives payment (net)
        $vendorWallet = Wallet::where('user_id', $this->vendor->id)->first();
        $expectedVendorAmount = 500000 * 0.9; // 10% platform fee
        expect($vendorWallet->balance)->toBe($expectedVendorAmount);

        // Verify admin receives fee
        $adminWallet = Wallet::where('user_id', $this->admin->id)->first();
        $expectedAdminFee = 500000 * 0.1; // 10% platform fee
        expect($adminWallet->balance)->toBe($expectedAdminFee);

        // Verify escrow ledgers created
        $releaseLogs = EscrowLedger::where('service_order_id', $this->order->id)
            ->where('type', 'release')
            ->get();
        expect($releaseLogs->count())->toBeGreaterThan(0);

        $feeLogs = EscrowLedger::where('service_order_id', $this->order->id)
            ->where('type', 'fee')
            ->get();
        expect($feeLogs->count())->toBeGreaterThan(0);
    });

    it('prevents payment release without admin verification', function () {
        // Fund escrow
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 500000]);

        $this->order->refresh();

        // Vendor submits work
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Work completed',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        // Buyer approves
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order));

        $this->order->refresh();

        // Try to release payment without admin verification - should fail
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.release-escrow', $this->order), ['amount' => 500000])
            ->assertSessionHasErrors();

        // Verify escrow still in order
        $this->order->refresh();
        expect($this->order->escrow_amount)->toBe(500000.00);
    });

    it('prevents release if work not verified', function () {
        // Fund escrow
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 500000]);

        $this->order->refresh();

        // Try to release without work submission
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.release-escrow', $this->order), ['amount' => 500000])
            ->assertSessionHasErrors();

        expect($this->order->escrow_amount)->toBe(500000.00);
    });
});

/**
 * Rejection Flow Tests
 */
describe('Studio Payment Verification - Rejection Flow', function () {

    beforeEach(function () {
        Storage::fake('public');

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        Wallet::create(['user_id' => $this->buyer->id, 'balance' => 5000000, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->admin->id, 'balance' => 0, 'currency' => 'IDR']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Design Website',
            'description' => 'Design a professional website',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('allows buyer to reject work submission', function () {
        // Vendor submits work
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Poor quality work',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        // Buyer rejects
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.reject-work', $this->order), [
                'notes' => 'The design does not meet the requirements. Please redesign.',
            ])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->work_status)->toBe('rejected');
        expect($this->order->buyer_approval_status)->toBe('rejected');

        // Verify approval log created
        $rejectLog = ApprovalLog::where('service_order_id', $this->order->id)
            ->where('action', 'work_rejected')
            ->first();
        expect($rejectLog)->not->toBeNull();
    });

    it('admin can reject work and refund escrow', function () {
        // Vendor submits work
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Suspicious work',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        // Buyer approves
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order));

        // Admin rejects work
        $this->actingAs($this->admin)
            ->post(route('admin.order-verification.reject', $this->order), [
                'notes' => 'Work violates content policy.',
            ])
            ->assertRedirect();

        $this->order->refresh();
        expect($this->order->status)->toBe('rejected');

        // Verify escrow refunded to buyer
        $buyerWallet = Wallet::where('user_id', $this->buyer->id)->first();
        expect($buyerWallet->balance)->toBe(5000000.00); // Full refund

        // Verify refund ledger
        $refundLog = EscrowLedger::where('service_order_id', $this->order->id)
            ->where('type', 'refund')
            ->first();
        expect($refundLog)->not->toBeNull();
    });
});

/**
 * Permission and Authorization Tests
 */
describe('Studio Payment Verification - Permissions', function () {

    beforeEach(function () {
        Storage::fake('public');

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->otherVendor = User::factory()->create(['role' => 'seller']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        Wallet::create(['user_id' => $this->buyer->id, 'balance' => 5000000, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Design Website',
            'description' => 'Design a professional website',
            'budget' => 500000,
            'status' => 'in_progress',
            'escrow_amount' => 500000,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('prevents non-assigned vendor from submitting work', function () {
        $this->actingAs($this->otherVendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'I will do this work',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ])
            ->assertForbidden();
    });

    it('prevents non-buyer from approving work', function () {
        // Vendor submits work
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Work done',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        // Other user tries to approve
        $this->actingAs($this->otherVendor)
            ->post(route('studio.orders.approve-work', $this->order), [
                'notes' => 'Approved',
            ])
            ->assertForbidden();
    });

    it('prevents non-admin from verifying orders', function () {
        // Vendor submits and buyer approves
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Work done',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order));

        // Non-admin tries to verify
        $this->actingAs($this->vendor)
            ->post(route('admin.order-verification.verify', $this->order), [
                'notes' => 'Verified',
            ])
            ->assertForbidden();
    });
});

/**
 * Wallet and Balance Tests
 */
describe('Studio Payment Verification - Wallets', function () {

    beforeEach(function () {
        Storage::fake('public');

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->buyerWallet = Wallet::create(['user_id' => $this->buyer->id, 'balance' => 5000000, 'currency' => 'IDR']);
        $this->vendorWallet = Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);
        $this->adminWallet = Wallet::create(['user_id' => $this->admin->id, 'balance' => 0, 'currency' => 'IDR']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Service',
            'description' => 'Service',
            'budget' => 1000000,
            'status' => 'quoted',
            'escrow_amount' => 0,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('deducts correct amount from buyer wallet on escrow funding', function () {
        $initialBalance = $this->buyerWallet->balance;

        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 1000000]);

        $this->buyerWallet->refresh();
        expect($this->buyerWallet->balance)->toBe($initialBalance - 1000000);
    });

    it('credits vendor correctly with 10% platform fee deducted', function () {
        // Fund escrow
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 1000000]);

        // Submit and approve
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Done',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order));

        // Verify and release
        $this->actingAs($this->admin)
            ->post(route('admin.order-verification.verify', $this->order), [
                'notes' => 'Verified',
            ]);

        $this->vendorWallet->refresh();
        $expectedAmount = 1000000 * 0.9; // 10% fee
        expect($this->vendorWallet->balance)->toBe($expectedAmount);
    });

    it('credits admin correctly with platform fee', function () {
        // Fund escrow
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 1000000]);

        // Submit and approve
        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Done',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ]);

        $this->actingAs($this->buyer)
            ->post(route('studio.orders.approve-work', $this->order));

        // Verify and release
        $this->actingAs($this->admin)
            ->post(route('admin.order-verification.verify', $this->order), [
                'notes' => 'Verified',
            ]);

        $this->adminWallet->refresh();
        $expectedFee = 1000000 * 0.1; // 10% fee
        expect($this->adminWallet->balance)->toBe($expectedFee);
    });
});

/**
 * Error Scenario Tests
 */
describe('Studio Payment Verification - Error Scenarios', function () {

    beforeEach(function () {
        Storage::fake('public');

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->vendor = User::factory()->create(['role' => 'seller']);

        // Buyer with insufficient balance
        Wallet::create(['user_id' => $this->buyer->id, 'balance' => 100000, 'currency' => 'IDR']);
        Wallet::create(['user_id' => $this->vendor->id, 'balance' => 0, 'currency' => 'IDR']);

        $this->order = ServiceOrder::create([
            'user_id' => $this->buyer->id,
            'assigned_user_id' => $this->vendor->id,
            'title' => 'Service',
            'description' => 'Service',
            'budget' => 500000,
            'status' => 'quoted',
            'escrow_amount' => 0,
            'work_status' => 'not_submitted',
            'buyer_approval_status' => 'pending',
        ]);
    });

    it('prevents funding with insufficient wallet balance', function () {
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 500000])
            ->assertSessionHasErrors();
    });

    it('requires minimum description length on work submission', function () {
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 100000]);

        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'Short',
                'files' => [UploadedFile::fake()->create('file.pdf')],
            ])
            ->assertSessionHasErrors('description');
    });

    it('enforces maximum file count limit', function () {
        $this->actingAs($this->buyer)
            ->post(route('studio.orders.fund-escrow', $this->order), ['amount' => 100000]);

        $files = collect(range(1, 15))
            ->map(fn($i) => UploadedFile::fake()->create("file$i.pdf"))
            ->toArray();

        $this->actingAs($this->vendor)
            ->post(route('studio.orders.submit-work.store', $this->order), [
                'description' => 'This is a valid description with minimum length required',
                'files' => $files,
            ])
            ->assertSessionHasErrors('files');
    });
});
