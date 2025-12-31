<?php

namespace App\Services;

use App\Models\User;
use App\Models\Clip;
use Illuminate\Support\Facades\DB;

class ClipperOnboardingService
{
    /**
     * Register a clipper (user requests clipper role).
     */
    public function registerClipper(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user with clipper information
            $user->update([
                'clipper_role' => 'clipper',
                'portfolio_url' => $data['portfolio_url'] ?? $user->portfolio_url,
                'skills' => $data['skills'] ?? $user->skills,
            ]);

            // Create clipper wallet if not exists
            $walletService = app(\App\Services\WalletService::class);
            $walletService->getClipperWallet($user);

            // Create audit log
            \App\Models\AuditLog::logAction([
                'user_id' => $user->id,
                'action' => 'register_clipper',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => [
                    'clipper_role' => 'clipper',
                    'portfolio_url' => $data['portfolio_url'] ?? null,
                ],
            ]);

            return true;
        });
    }

    /**
     * Update clipper profile.
     */
    public function updateProfile(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'portfolio_url' => $data['portfolio_url'] ?? $user->portfolio_url,
                'website_url' => $data['website_url'] ?? $user->website_url,
                'skills' => $data['skills'] ?? $user->skills,
                'goals' => $data['goals'] ?? $user->goals,
            ]);

            // Create audit log
            \App\Models\AuditLog::logAction([
                'user_id' => $user->id,
                'action' => 'update_clipper_profile',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => $data,
            ]);

            return true;
        });
    }

    /**
     * Verify clipper (auto or manual).
     */
    public function verifyClipper(User $user, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($user, $admin) {
            // For now, we'll use a simple verification flag
            // You might want to add a 'is_verified_clipper' field to users table
            // For this implementation, we'll assume verification is automatic
            // or handled through admin action

            // Create audit log
            \App\Models\AuditLog::logAction([
                'admin_id' => $admin?->id,
                'action' => 'verify_clipper',
                'target_type' => 'user',
                'target_id' => $user->id,
                'new_value' => ['verified' => true],
            ]);

            return true;
        });
    }

    /**
     * Get clipper statistics.
     */
    public function getClipperStats(User $user): array
    {
        $clips = $user->clips;
        $totalClips = $clips->count();
        $approvedClips = $clips->where('status', 'approved')->count();
        $pendingClips = $clips->where('status', 'pending')->count();
        $rejectedClips = $clips->where('status', 'rejected')->count();
        
        $totalViews = $clips->sum('valid_views');
        $totalReward = $clips->sum('approved_reward');
        $totalPaid = $clips->where('status', 'paid')->sum('approved_reward');

        $clipperWallet = app(\App\Services\WalletService::class)->getClipperWallet($user);

        return [
            'total_clips' => $totalClips,
            'approved_clips' => $approvedClips,
            'pending_clips' => $pendingClips,
            'rejected_clips' => $rejectedClips,
            'total_views' => $totalViews,
            'total_reward' => $totalReward,
            'total_paid' => $totalPaid,
            'wallet_balance' => [
                'pending' => (float) $clipperWallet->balance_pending,
                'available' => (float) $clipperWallet->balance_available,
                'withdrawn' => (float) $clipperWallet->balance_withdrawn,
            ],
        ];
    }
}

