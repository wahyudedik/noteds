<?php

namespace App\Services;

use App\Models\User;
use App\Models\Certification;
use App\Models\UserCertification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class CertificationService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Apply for certification
     */
    public function applyForCertification(
        User $user,
        Certification $certification,
        ?string $applicationNotes = null,
        ?array $evidence = null
    ): UserCertification {
        // Check if already applied
        $existing = UserCertification::where('user_id', $user->id)
            ->where('certification_id', $certification->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            throw new \Exception('You have already applied for or received this certification.');
        }

        // Create application
        $userCertification = UserCertification::create([
            'user_id' => $user->id,
            'certification_id' => $certification->id,
            'status' => $certification->requires_approval ? 'pending' : 'approved',
            'application_notes' => $applicationNotes,
            'evidence' => $evidence,
            'applied_at' => now(),
        ]);

        // Auto-approve if no approval required
        if (!$certification->requires_approval) {
            $userCertification->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => null, // Auto-approved
            ]);

            $this->notificationService->create(
                $user,
                'certification_approved',
                '🎓 Certification Approved!',
                "Congratulations! Your {$certification->name} certification has been approved.",
                route('profile.edit'),
                ['certification_id' => $certification->id]
            );
        } else {
            // Notify admins
            $this->notifyAdminsOfApplication($user, $certification, $userCertification);
        }

        return $userCertification;
    }

    /**
     * Approve certification
     */
    public function approveCertification(
        UserCertification $userCertification,
        User $approver,
        ?string $adminNotes = null,
        ?\Carbon\Carbon $expiresAt = null
    ): void {
        $userCertification->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'admin_notes' => $adminNotes,
            'approved_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Notify user
        $this->notificationService->create(
            $userCertification->user,
            'certification_approved',
            '🎓 Certification Approved!',
            "Congratulations! Your {$userCertification->certification->name} certification has been approved.",
            route('profile.edit'),
            [
                'certification_id' => $userCertification->certification_id,
                'approved_by' => $approver->name,
            ]
        );
    }

    /**
     * Reject certification
     */
    public function rejectCertification(
        UserCertification $userCertification,
        User $rejector,
        string $adminNotes
    ): void {
        $userCertification->update([
            'status' => 'rejected',
            'approved_by' => $rejector->id,
            'admin_notes' => $adminNotes,
            'rejected_at' => now(),
        ]);

        // Notify user
        $this->notificationService->create(
            $userCertification->user,
            'certification_rejected',
            '❌ Certification Application Rejected',
            "Your {$userCertification->certification->name} certification application was rejected. Reason: {$adminNotes}",
            route('profile.edit'),
            [
                'certification_id' => $userCertification->certification_id,
                'rejected_by' => $rejector->name,
            ]
        );
    }

    /**
     * Revoke certification
     */
    public function revokeCertification(
        UserCertification $userCertification,
        User $revoker,
        string $reason
    ): void {
        $userCertification->update([
            'status' => 'rejected',
            'admin_notes' => "Revoked: {$reason}",
            'approved_by' => $revoker->id,
            'rejected_at' => now(),
        ]);

        // Notify user
        $this->notificationService->create(
            $userCertification->user,
            'certification_revoked',
            '⚠️ Certification Revoked',
            "Your {$userCertification->certification->name} certification has been revoked. Reason: {$reason}",
            route('profile.edit'),
            [
                'certification_id' => $userCertification->certification_id,
                'revoked_by' => $revoker->name,
            ]
        );
    }

    /**
     * Notify admins of new certification application
     */
    protected function notifyAdminsOfApplication(
        User $user,
        Certification $certification,
        UserCertification $userCertification
    ): void {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                $admin,
                'admin_certification_application',
                '📋 New Certification Application',
                "{$user->name} has applied for {$certification->name} certification.",
                route('admin.certifications.applications.show', $userCertification),
                [
                    'user_id' => $user->id,
                    'certification_id' => $certification->id,
                    'application_id' => $userCertification->id,
                ]
            );
        }
    }

    /**
     * Check and expire certifications
     */
    public function expireCertifications(): int
    {
        $expired = UserCertification::where('status', 'approved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $certification) {
            $certification->update(['status' => 'expired']);
            
            $this->notificationService->create(
                $certification->user,
                'certification_expired',
                '⏰ Certification Expired',
                "Your {$certification->certification->name} certification has expired.",
                route('profile.edit'),
                ['certification_id' => $certification->certification_id]
            );
            
            $count++;
        }

        return $count;
    }
}

