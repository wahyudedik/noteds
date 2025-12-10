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
     * Seller/Buyer Dashboard
     * This is for non-admin users only
     */
    public function index(): ViewContract|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Block admin dari mengakses user dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Check if user needs to complete profile (KTP and selfie) - redirect once after register
        // Check session to see if this is first time after register
        if (!$user->ktp_path || !$user->selfie_path) {
            // Only redirect if coming from registration (check session or recent registration)
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

        return view('dashboard');
    }
}
