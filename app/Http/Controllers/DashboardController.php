<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View as ViewContract;

class DashboardController extends Controller
{
    /**
     * Main Dashboard Router
     * Routes users to appropriate dashboard based on their role
     * Only accessible to non-admin users
     */
    public function index(): ViewContract|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Block admin dari mengakses user dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Check if user needs to complete profile (KTP and selfie)
        if (!$user->ktp_path || !$user->selfie_path) {
            $justRegistered = session('just_registered', false);
            if ($justRegistered || ($user->created_at->gt(now()->subMinutes(5)) && !$user->ktp_path && !$user->selfie_path)) {
                session()->forget('just_registered');
                return redirect()->route('profile.edit')
                    ->with('info', 'Silakan lengkapi profil Anda dengan mengupload KTP dan foto selfie untuk verifikasi identitas.');
            }
        }

        // Workspace users should be redirected to workspaces
        if ($user->role === 'user_workspaces') {
            return redirect()->route('workspaces.index');
        }

        // Route to role-specific dashboard
        if ($user->hasRole('seller')) {
            return app(SellerDashboardController::class)->index();
        }

        if ($user->hasRole('buyer')) {
            return app(BuyerDashboardController::class)->index();
        }

        // Default fallback (shouldn't reach here with proper role assignment)
        return app(BuyerDashboardController::class)->index();
    }
}
