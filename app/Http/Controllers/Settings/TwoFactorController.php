<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService
    ) {}

    /**
     * Show 2FA setup page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $isEnabled = $this->twoFactorService->isEnabled($user);
        
        $data = [
            'enabled' => $isEnabled,
        ];

        // If not enabled, generate setup data
        if (!$isEnabled) {
            $secret = $this->twoFactorService->generateSecretKey();
            $qrCodeUrl = $this->twoFactorService->getQRCodeUrl($user, $secret);
            
            // Store secret temporarily in session for verification
            session(['two_factor_secret' => Crypt::encryptString($secret)]);
            
            $data['secret'] = $secret;
            $data['qr_code_url'] = $qrCodeUrl;
        } else {
            // Show recovery codes if available
            if ($user->two_factor_recovery_codes) {
                try {
                    $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
                    if (is_array($recoveryCodes)) {
                        $data['recovery_codes_count'] = count($recoveryCodes);
                    }
                } catch (\Exception $e) {
                    // If decryption fails, treat as no codes
                    $data['recovery_codes_count'] = 0;
                }
            }
        }

        return Inertia::render('Settings/TwoFactor', $data);
    }

    /**
     * Enable 2FA.
     */
    public function enable(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        
        // Get secret from session
        $secret = session('two_factor_secret');
        if (!$secret) {
            return response()->json([
                'message' => 'Session expired. Please refresh and try again.',
            ], 422);
        }

        try {
            $secret = Crypt::decryptString($secret);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid session data. Please refresh and try again.',
            ], 422);
        }

        // Verify code (in production, use Google2FA library)
        // For now, this is a placeholder - implement with Google2FA
        // $isValid = $this->twoFactorService->verifyCode($user, $request->code);
        
        // TODO: Implement proper TOTP verification
        // For now, accept any 6-digit code as placeholder
        $isValid = preg_match('/^\d{6}$/', $request->code);

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        // Store encrypted secret and generate recovery codes
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes->toArray())),
            'two_factor_confirmed_at' => now(),
        ]);

        session()->forget('two_factor_secret');

        return response()->json([
            'message' => 'Two-factor authentication enabled successfully.',
            'recovery_codes' => $recoveryCodes->toArray(),
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        // Check if 2FA is required for admin
        if ($this->twoFactorService->isRequiredForAdmin($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is required for admin accounts.',
            ], 403);
        }

        $this->twoFactorService->disableTwoFactor($user);

        return response()->json([
            'message' => 'Two-factor authentication disabled successfully.',
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        
        if (!$this->twoFactorService->isEnabled($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        $codes = $this->twoFactorService->regenerateRecoveryCodes($user);

        return response()->json([
            'message' => 'Recovery codes regenerated successfully.',
            'recovery_codes' => $codes->toArray(),
        ]);
    }
}
