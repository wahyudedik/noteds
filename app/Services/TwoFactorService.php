<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Two-Factor Authentication Service
 * 
 * Note: For production use, install pragmarx/google2fa package:
 * composer require pragmarx/google2fa
 * 
 * Then update methods to use Google2FA library for TOTP generation/verification.
 */
class TwoFactorService
{
    /**
     * Generate a secret key for 2FA.
     * In production, use Google2FA::generateSecretKey()
     */
    public function generateSecretKey(): string
    {
        // Simple implementation - in production, use Google2FA library
        // For now, generate a random base32-like string
        return base64_encode(random_bytes(32));
    }

    /**
     * Generate QR code data URL for the secret.
     * In production, use Google2FA::getQRCodeUrl() with a QR code generator
     */
    public function getQRCodeUrl(User $user, string $secret): string
    {
        $issuer = config('app.name', 'Noteds');
        $label = $user->email;
        
        // Format: otpauth://totp/{issuer}:{label}?secret={secret}&issuer={issuer}
        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($label),
            $secret,
            rawurlencode($issuer)
        );

        // In production, generate QR code image using a library like simple-qrcode
        // For now, return the URL - frontend can use a QR code library
        return $url;
    }

    /**
     * Verify TOTP code.
     * In production, use Google2FA::verifyKey($secret, $code)
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        // Simple implementation - in production, use Google2FA library
        // This is a placeholder that always returns false for security
        // You MUST use a proper TOTP library like pragmarx/google2fa
        
        // TODO: Implement proper TOTP verification using Google2FA library
        // Example: return Google2FA::verifyKey($secret, $code);
        
        return false; // Placeholder - implement with Google2FA library
    }

    /**
     * Enable 2FA for a user.
     */
    public function enableTwoFactor(User $user, string $code): bool
    {
        if (!$this->verifyCode($user, $code)) {
            return false;
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return true;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes(): Collection
    {
        return collect(range(1, 8))->map(function () {
            return Str::random(10) . '-' . Str::random(10);
        });
    }

    /**
     * Regenerate recovery codes for a user.
     */
    public function regenerateRecoveryCodes(User $user): Collection
    {
        $codes = $this->generateRecoveryCodes();
        
        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(
                json_encode($codes->toArray())
            ),
        ]);

        return $codes;
    }

    /**
     * Verify recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        try {
            $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
            
            if (!is_array($codes) || !in_array($code, $codes)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        // Remove used code
        $codes = array_values(array_diff($codes, [$code]));
        
        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
        ]);

        return true;
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && $user->two_factor_confirmed_at !== null;
    }

    /**
     * Check if 2FA is required for admin users.
     */
    public function isRequiredForAdmin(User $user): bool
    {
        return $user->isAdmin() && config('auth.two_factor_required_for_admin', false);
    }
}

