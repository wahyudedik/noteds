<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private ReferralService $referralService,
        private NotificationService $notificationService
    ) {
    }

    /**
     * Redirect to OAuth provider.
     */
    public function redirect(string $provider, Request $request): RedirectResponse
    {
        $this->validateProvider($provider);

        // Store role and referral code in session for callback
        if ($request->has('role')) {
            session(['social_register_role' => $request->get('role')]);
        }
        if ($request->has('ref')) {
            session(['referral_code' => $request->get('ref')]);
        }
        if ($request->has('invite')) {
            session(['invite_token' => $request->get('invite')]);
        }

        // Configure scopes for different providers
        $socialite = Socialite::driver($provider);
        
        if ($provider === 'facebook') {
            $socialite->scopes(['email']);
        }
        
        if ($provider === 'google') {
            $socialite->scopes(['email', 'profile']);
        }

        return $socialite->redirect();
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(string $provider, Request $request): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }

        // Check if social account exists
        $socialAccount = SocialAccount::findByProvider($provider, $socialUser->getId());

        if ($socialAccount) {
            // User already exists, login
            Auth::login($socialAccount->user);
            $request->session()->regenerate();

            return $this->redirectAfterLogin($socialAccount->user);
        }

        // Check if user with same email exists
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link social account to existing user
            SocialAccount::findOrCreate(
                $user->id,
                $provider,
                $socialUser->getId(),
                [
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'data' => $socialUser->user,
                ]
            );

            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectAfterLogin($user);
        }

        // Check if email is available (required for registration)
        if (!$socialUser->getEmail()) {
            return redirect()->route('register')
                ->with('error', 'Unable to retrieve email from ' . ucfirst($provider) . '. Please register with email instead.');
        }

        // Create new user
        $user = $this->createUserFromSocial($socialUser, $provider, $request);

        // Create social account
        SocialAccount::findOrCreate(
            $user->id,
            $provider,
            $socialUser->getId(),
            [
                'token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'data' => $socialUser->user,
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        // Handle referral if exists
        $refCode = session('referral_code');
        if ($refCode) {
            $this->referralService->processReferral($refCode, $user);
            session()->forget('referral_code');
        }

        // Handle workspace invitation if exists
        $inviteToken = session('invite_token');
        if ($inviteToken) {
            $this->handleWorkspaceInvitation($inviteToken, $user);
            session()->forget('invite_token');
        }

        // Mark as just registered for profile completion check
        session(['just_registered' => true]);

        return $this->redirectAfterLogin($user);
    }

    /**
     * Create user from social provider data.
     */
    private function createUserFromSocial($socialUser, string $provider, Request $request): User
    {
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        $avatar = $socialUser->getAvatar();

        // Generate unique username
        $baseUsername = Str::slug($name);
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Download and save avatar
        $avatarPath = null;
        if ($avatar) {
            $avatarPath = $this->downloadAvatar($avatar, $username);
        }

        // Get role from session or request or default
        $role = session('social_register_role') ?? $request->get('role', 'buyer');
        session()->forget('social_register_role');

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Random password for social login
            'role' => $role,
            'avatar' => $avatarPath,
            'email_verified_at' => now(), // Social login emails are pre-verified
            'agreement_accepted_at' => now(),
            'agreement_version' => 1,
        ]);

        // Notify admin about new user
        $this->notificationService->notifyAdminNewUserRegistration($user);

        return $user;
    }

    /**
     * Download avatar from social provider and save locally.
     */
    private function downloadAvatar(string $url, string $username): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return null;
            }

            $imageData = $response->body();
            $extension = $this->getImageExtension($imageData, $url);
            $filename = 'avatars/' . $username . '_' . time() . '.' . $extension;
            
            Storage::disk('public')->put($filename, $imageData);
            
            return $filename;
        } catch (\Exception $e) {
            logger()->warning('Failed to download avatar from social provider', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get image extension from image data or URL.
     */
    private function getImageExtension(string $imageData, string $url): string
    {
        // Try to detect from image data
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (isset($extensions[$mimeType])) {
            return $extensions[$mimeType];
        }

        // Fallback to URL extension
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $extension : 'jpg';
    }

    /**
     * Handle workspace invitation.
     */
    private function handleWorkspaceInvitation(string $token, User $user): void
    {
        $invitation = \App\Models\WorkspaceInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($invitation) {
            $invitation->accept($user);
        }
    }

    /**
     * Redirect after login.
     */
    private function redirectAfterLogin(User $user): RedirectResponse
    {
        // Check if user needs to complete profile
        if (!$user->hasRole('admin') && (!$user->ktp_path || !$user->selfie_path)) {
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

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Validate OAuth provider.
     */
    private function validateProvider(string $provider): void
    {
        $allowedProviders = ['google', 'facebook', 'github'];
        
        if (!in_array($provider, $allowedProviders)) {
            abort(404, 'Provider not supported');
        }
    }
}
