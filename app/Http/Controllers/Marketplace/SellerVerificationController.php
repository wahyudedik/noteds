<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\SellerVerificationService;
use App\Http\Requests\ApplySellerVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerVerificationController extends Controller
{
    public function __construct(
        private SellerVerificationService $verificationService
    ) {}

    /**
     * Show verification status and application form.
     */
    public function show(Request $request): Response
    {
        $seller = $request->user();
        $verification = $this->verificationService->getVerificationStatus($seller);
        $canApply = $this->verificationService->canApply($seller);
        $eligibilityErrors = count($canApply) > 0 ? $canApply : [];

        return Inertia::render('Marketplace/Seller/Verification/Show', [
            'verification' => $verification?->load(['verifiedBy', 'revokedBy']),
            'is_verified' => $seller->isVerifiedSeller(),
            'can_apply' => empty($eligibilityErrors),
            'eligibility_errors' => $eligibilityErrors,
        ]);
    }

    /**
     * Submit verification application.
     */
    public function apply(ApplySellerVerificationRequest $request)
    {
        $seller = $request->user();

        // Check eligibility
        $eligibilityErrors = $this->verificationService->canApply($seller);
        if (!empty($eligibilityErrors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot apply for verification',
                    'errors' => $eligibilityErrors,
                ], 422);
            }

            return back()->withErrors(['eligibility' => $eligibilityErrors]);
        }

        $applicationData = $request->validated();
        $verification = $this->verificationService->applyForVerification($seller, $applicationData);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification application submitted successfully',
                'verification' => $verification,
            ], 201);
        }

        return redirect()
            ->route('marketplace.seller.verification')
            ->with('success', 'Verification application submitted successfully');
    }

    /**
     * Get current verification status (API).
     */
    public function status(Request $request)
    {
        $seller = $request->user();
        $verification = $this->verificationService->getVerificationStatus($seller);

        return response()->json([
            'verification' => $verification?->load(['verifiedBy', 'revokedBy']),
            'is_verified' => $seller->isVerifiedSeller(),
        ]);
    }
}
