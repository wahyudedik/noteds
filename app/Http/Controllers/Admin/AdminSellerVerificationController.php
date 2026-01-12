<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SellerVerification;
use App\Services\SellerVerificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSellerVerificationController extends Controller
{
    public function __construct(
        private SellerVerificationService $verificationService
    ) {}

    /**
     * List all verification applications.
     */
    public function index(Request $request): Response
    {
        $status = $request->get('status', 'pending'); // pending, approved, rejected, all
        $search = $request->get('search');

        $query = SellerVerification::with(['seller']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('seller', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $verifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/SellerVerifications/Index', [
            'verifications' => $verifications,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Show application details.
     */
    public function show(SellerVerification $verification): Response
    {
        $verification->load(['seller', 'verifiedBy', 'revokedBy']);

        return Inertia::render('Admin/SellerVerifications/Show', [
            'verification' => $verification,
        ]);
    }

    /**
     * Approve application.
     */
    public function approve(Request $request, SellerVerification $verification)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->verificationService->approveVerification(
            $verification,
            $request->user(),
            $validated['notes'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification approved successfully',
                'verification' => $verification->fresh()->load(['seller', 'verifiedBy']),
            ]);
        }

        return redirect()
            ->route('admin.seller-verifications.show', $verification)
            ->with('success', 'Verification approved successfully');
    }

    /**
     * Reject application.
     */
    public function reject(Request $request, SellerVerification $verification)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->verificationService->rejectVerification(
            $verification,
            $request->user(),
            $validated['reason']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification rejected successfully',
                'verification' => $verification->fresh()->load(['seller', 'verifiedBy']),
            ]);
        }

        return redirect()
            ->route('admin.seller-verifications.show', $verification)
            ->with('success', 'Verification rejected successfully');
    }

    /**
     * Revoke existing verification.
     */
    public function revoke(Request $request, User $seller)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->verificationService->revokeVerification(
            $seller,
            $request->user(),
            $validated['reason']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification revoked successfully',
            ]);
        }

        return redirect()
            ->route('admin.seller-verifications.index')
            ->with('success', 'Verification revoked successfully');
    }
}
