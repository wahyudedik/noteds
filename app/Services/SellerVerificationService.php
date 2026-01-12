<?php

namespace App\Services;

use App\Models\User;
use App\Models\SellerVerification;
use Illuminate\Support\Facades\Storage;
use App\Events\SellerVerified;

class SellerVerificationService
{
    /**
     * Apply for verification.
     */
    public function applyForVerification(User $seller, array $applicationData): SellerVerification
    {
        // Store documents
        $documents = [];
        if (isset($applicationData['documents']) && is_array($applicationData['documents'])) {
            foreach ($applicationData['documents'] as $document) {
                if ($document instanceof \Illuminate\Http\UploadedFile) {
                    $path = $document->store('verifications', 'public');
                    $documents[] = $path;
                }
            }
        }

        $applicationData['documents'] = $documents;

        return SellerVerification::create([
            'user_id' => $seller->id,
            'status' => 'pending',
            'application_data' => $applicationData,
        ]);
    }

    /**
     * Approve verification.
     */
    public function approveVerification(SellerVerification $verification, User $admin, ?string $notes = null): void
    {
        $verification->approve($admin, $notes);

        event(new SellerVerified($verification->seller));
    }

    /**
     * Reject verification.
     */
    public function rejectVerification(SellerVerification $verification, User $admin, string $reason): void
    {
        $verification->reject($admin, $reason);
    }

    /**
     * Revoke verification.
     */
    public function revokeVerification(User $seller, User $admin, string $reason): void
    {
        $verification = SellerVerification::where('user_id', $seller->id)
            ->where('status', 'approved')
            ->first();

        if ($verification) {
            $verification->revoke($admin, $reason);
        } else {
            // Direct revocation if no verification record exists
            $seller->update(['is_verified_seller' => false]);
        }
    }

    /**
     * Check if seller can apply.
     */
    public function canApply(User $seller): array
    {
        $errors = [];

        if ($seller->isVerifiedSeller()) {
            $errors[] = 'Seller is already verified';
        }

        $existingVerification = $this->getVerificationStatus($seller);
        if ($existingVerification && $existingVerification->isPending()) {
            $errors[] = 'Seller already has a pending verification application';
        }

        if (config('seller.verification.require_email_verification', true) && !$seller->hasVerifiedEmail()) {
            $errors[] = 'Email must be verified before applying for seller verification';
        }

        $minProducts = config('seller.verification.min_products_required', 1);
        if ($seller->products()->count() < $minProducts) {
            $errors[] = "Seller must have at least {$minProducts} product(s) to apply for verification";
        }

        return $errors;
    }

    /**
     * Get verification status.
     */
    public function getVerificationStatus(User $seller): ?SellerVerification
    {
        return SellerVerification::where('user_id', $seller->id)->first();
    }
}

