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
            // Check if user already has approved brand role
            if ($user->clipper_role === 'brand') {
                throw new \Exception('You are already registered as a brand.');
            }

            // Check if user has pending registration (only prevent if pending, allow if rejected)
            $pendingRegistration = \App\Models\BrandRegistration::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingRegistration) {
                throw new \Exception('You already have a pending brand registration. Please wait for admin approval.');
            }

            // Update user with brand information (map form fields to user fields)
            $user->update([
                'business_name' => $data['company_name'] ?? $user->business_name,
                'business_field' => $data['business_type'] ?? $user->business_field,
                'website_url' => $data['website'] ?? $user->website_url,
                // Keep clipper_role as null until approved
            ]);

            // Map form fields to BrandRegistration fields
            $registrationData = [
                'company_name' => $data['company_name'] ?? null,
                'business_type' => $data['business_type'] ?? null,
                'website' => $data['website'] ?? null,
                'contact_person' => $data['contact_person_name'] ?? null,
                'phone' => $data['contact_person_phone'] ?? $data['phone'] ?? null,
                'status' => 'pending',
            ];

            // Create BrandRegistration record for admin approval
            $registration = \App\Models\BrandRegistration::create(array_merge(
                $registrationData,
                ['user_id' => $user->id]
            ));

            // Notify admins about new registration
            $this->notificationService->notifyBrandRegistration($registration);

            // Create audit log
            \App\Models\AuditLog::logAction([
                'user_id' => $user->id,
                'action' => 'register_brand',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => [
                    'company_name' => $data['company_name'] ?? null,
                    'business_type' => $data['business_type'] ?? null,
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

