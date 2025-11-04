<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        
        return view('auth.register', compact('refCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, ReferralService $referralService): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:buyer,seller'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);

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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'referred_by' => $referrer?->id,
        ]);
        
        // Assign Spatie role
        $user->assignRole($request->role);

        // Generate referral code for new user
        $user->generateReferralCode();

        event(new Registered($user));

        // Process signup reward if referred
        if ($referrer) {
            $referralService->processSignupReward($user);
        }

        Auth::login($user);

        // Redirect to dashboard (username is already set)
        return redirect(route('dashboard', absolute: false));
    }
}
