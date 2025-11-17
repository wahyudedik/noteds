<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkspaceActivityLog;
use App\Models\WorkspaceMember;
use App\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // Get referral code from URL parameter
        $refCode = $request->get('ref');
        
        // Check for workspace invitation token
        $inviteToken = $request->get('invite');
        $invitation = null;
        if ($inviteToken) {
            $invitation = \App\Models\WorkspaceInvitation::where('token', $inviteToken)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->with('workspace')
                ->first();
        }
        
        return view('auth.register', compact('refCode', 'invitation', 'inviteToken'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ReferralService $referralService): RedirectResponse
    {
        // Check for workspace invitation
        $invitation = null;
        $inviteToken = $request->get('invite_token');
        if ($inviteToken) {
            $invitation = \App\Models\WorkspaceInvitation::where('token', $inviteToken)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first();
            
            if (!$invitation) {
                return redirect()->route('register', ['invite' => $inviteToken])
                    ->withInput()
                    ->with('error', 'Invitation tidak valid atau sudah kadaluarsa.');
            }
            
            // Validate email matches invitation (case-insensitive)
            if (strtolower($invitation->email) !== strtolower($request->email)) {
                return redirect()->route('register', ['invite' => $inviteToken])
                    ->withInput()
                    ->with('error', 'Email harus sesuai dengan email yang di-invite: ' . $invitation->email);
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:buyer,seller,user_workspaces'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'invite_token' => ['nullable', 'string'],
            'agree_terms' => ['accepted'],
            // KTP dan selfie upload dipindahkan ke profile
        ]);

        // If invitation exists, role must be user_workspaces
        if ($invitation && $request->role !== 'user_workspaces') {
            return redirect()->route('register', ['invite' => $inviteToken])
                ->withInput()
                ->with('error', 'Untuk menerima invitation workspace, role harus Workspace User.');
        }

        // Find referrer if referral code provided
        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        // Auto-generate username from name
        $baseUsername = Str::slug(Str::lower($request->name), '');
        $username = $baseUsername;
        $counter = 1;
        
        // Ensure username is at least 3 characters and unique
        while (strlen($username) < 3 || User::where('username', $username)->exists()) {
            if (strlen($baseUsername) < 3) {
                $username = $baseUsername . str_pad((string)$counter, 3 - strlen($baseUsername), '0', STR_PAD_LEFT);
            } else {
                $username = $baseUsername . $counter;
            }
            $counter++;
        }

        // KTP dan selfie upload dipindahkan ke profile
        // User akan diminta melengkapi di profile setelah registrasi

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'referred_by' => $referrer?->id,
            'is_active' => true,
            'suspended_at' => null,
            'agreement_accepted_at' => now(),
            'agreement_version' => 'v1',
            'ktp_path' => null, // Akan diisi di profile
            'selfie_path' => null, // Akan diisi di profile
            'verification_status' => 'pending',
        ]);
        
        // Assign Spatie role
        $user->assignRole($request->role);

        // Generate referral code for new user (only if not workspace user)
        if ($request->role !== 'user_workspaces') {
            $user->generateReferralCode();
        }

        event(new Registered($user));

        // Notify admins about new user registration
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyAdminNewUserRegistration($user);

        // Process signup reward if referred (only for buyer/seller)
        if ($referrer && $request->role !== 'user_workspaces') {
            $referralService->processSignupReward($user);
        }

        // Handle workspace invitation acceptance
        if ($invitation) {
            $invitation->update([
                'accepted_at' => now(),
                'accepted_by' => $user->id,
            ]);
            
            // Add user to workspace
            WorkspaceMember::create([
                'workspace_id' => $invitation->workspace->id,
                'user_id' => $user->id,
                'role' => $invitation->role,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            WorkspaceActivityLog::record(
                $invitation->workspace,
                'member_joined',
                $user,
                [
                    'role' => $invitation->role,
                    'invited_by' => $invitation->invited_by,
                ]
            );
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        
        // Mark that user just registered for dashboard redirect check
        session()->put('just_registered', true);

        // Redirect based on role
        if ($request->role === 'user_workspaces') {
            // Redirect to workspace if invitation exists
            if ($invitation) {
                return redirect()->route('workspaces.show', $invitation->workspace)
                    ->with('success', 'Selamat datang di workspace! Anda telah bergabung sebagai ' . $invitation->role . '.');
            }
            return redirect()->route('workspaces.index');
        }

        // Redirect to profile untuk melengkapi KTP dan selfie (sekali saja setelah register)
        // Jika belum lengkap, redirect ke profile
        if (!$user->ktp_path || !$user->selfie_path) {
            return redirect()->route('profile.edit')
                ->with('info', 'Silakan lengkapi profil Anda dengan mengupload dokumen identitas (KTP atau Kartu Pelajar) dan foto selfie untuk verifikasi identitas.');
        }

        // Redirect to dashboard (username is already set)
        return redirect(route('dashboard', absolute: false));
    }
}
