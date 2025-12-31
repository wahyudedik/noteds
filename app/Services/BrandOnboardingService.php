<?php

namespace App\Services;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class BrandOnboardingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Register a brand (user requests brand role).
     */
    public function registerBrand(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user with brand information
            $user->update([
                'business_name' => $data['business_name'] ?? $user->business_name,
                'business_field' => $data['business_field'] ?? $user->business_field,
                'website_url' => $data['website_url'] ?? $user->website_url,
                'portfolio_url' => $data['portfolio_url'] ?? $user->portfolio_url,
                // Keep clipper_role as null until approved
            ]);

            // Create audit log
            \App\Models\AuditLog::logAction([
                'user_id' => $user->id,
                'action' => 'register_brand',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => [
                    'business_name' => $data['business_name'] ?? null,
                    'business_field' => $data['business_field'] ?? null,
                ],
            ]);

            return true;
        });
    }

    /**
     * Approve brand registration.
     */
    public function approveBrand(User $user, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($user, $admin) {
            $user->update(['clipper_role' => 'brand']);

            // Create audit log
            \App\Models\AuditLog::logAction([
                'admin_id' => $admin?->id,
                'action' => 'approve_brand',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => ['clipper_role' => 'brand'],
            ]);

            // Send approval notification
            $this->sendApprovalNotification($user, true);

            return true;
        });
    }

    /**
     * Reject brand registration.
     */
    public function rejectBrand(User $user, string $reason, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($user, $reason, $admin) {
            // Create audit log
            \App\Models\AuditLog::logAction([
                'admin_id' => $admin?->id,
                'action' => 'reject_brand',
                'target_type' => 'user',
                'target_id' => $user->id,
                'notes' => $reason,
            ]);

            // Send rejection notification
            $this->sendApprovalNotification($user, false, $reason);

            return true;
        });
    }

    /**
     * Get pending brand registrations.
     */
    public function getPendingBrands()
    {
        return User::whereNull('clipper_role')
            ->whereNotNull('business_name')
            ->latest()
            ->get();
    }

    /**
     * Send approval/rejection notification.
     */
    public function sendApprovalNotification(User $user, bool $approved, ?string $reason = null): void
    {
        if ($approved) {
            $this->notificationService->notifyBrandApproved($user);
        } else {
            // You might want to create a BrandRejectedNotification
            $user->notify(new \App\Notifications\BrandRejectedNotification($reason));
        }
    }
}

