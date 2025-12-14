<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Encryption\Encrypter;

/**
 * Encryption Service untuk Sensitive Data
 * Handles encryption/decryption of PII dan sensitive information
 */
class EncryptionService
{
    /**
     * Encrypt sensitive data
     * @param mixed $data Data to encrypt
     * @return string Encrypted string
     */
    public static function encrypt($data): string
    {
        return Crypt::encryptString(json_encode($data));
    }

    /**
     * Decrypt encrypted data
     * @param string $encrypted Encrypted string
     * @return mixed Decrypted data
     */
    public static function decrypt(string $encrypted): mixed
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true) ?? $decrypted;
        } catch (\Exception $e) {
            \Log::error('Decryption failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Encrypt SSN/ID number
     */
    public static function encryptSsn(string $ssn): string
    {
        // Validate format first
        if (!preg_match('/^[0-9\-]{11,19}$/', $ssn)) {
            throw new \InvalidArgumentException('Invalid SSN format');
        }

        return Crypt::encryptString($ssn);
    }

    /**
     * Decrypt SSN/ID number
     */
    public static function decryptSsn(string $encrypted): string
    {
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            \Log::error('SSN decryption failed');
            return '';
        }
    }

    /**
     * Hash sensitive data (one-way)
     */
    public static function hashSensitive(string $data): string
    {
        return hash('sha256', $data . config('app.key'));
    }

    /**
     * Verify hashed sensitive data
     */
    public static function verifySensitive(string $data, string $hash): bool
    {
        return hash_equals($hash, self::hashSensitive($data));
    }

    /**
     * Encrypt bank account details
     */
    public static function encryptBankAccount(array $account): string
    {
        if (!isset($account['number'], $account['bank'])) {
            throw new \InvalidArgumentException('Missing required account fields');
        }

        // Validate account number
        if (!preg_match('/^[0-9]{8,20}$/', $account['number'])) {
            throw new \InvalidArgumentException('Invalid account number');
        }

        return Crypt::encryptString(json_encode([
            'bank' => $account['bank'],
            'number' => $account['number'],
            'holder' => $account['holder'] ?? null,
        ]));
    }

    /**
     * Decrypt bank account details
     */
    public static function decryptBankAccount(string $encrypted): array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            \Log::error('Bank account decryption failed');
            return [];
        }
    }

    /**
     * Generate secure token
     */
    public static function generateSecureToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Encrypt payment token
     */
    public static function encryptPaymentToken(string $token): string
    {
        if (strlen($token) < 16) {
            throw new \InvalidArgumentException('Invalid payment token');
        }

        return Crypt::encryptString($token);
    }

    /**
     * Decrypt payment token
     */
    public static function decryptPaymentToken(string $encrypted): string
    {
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            \Log::error('Payment token decryption failed');
            return '';
        }
    }

    /**
     * Mask sensitive data for display (last 4 digits only)
     */
    public static function maskSensitiveData(string $data, int $showLast = 4): string
    {
        if (strlen($data) <= $showLast) {
            return str_repeat('*', strlen($data));
        }

        $visible = substr($data, -$showLast);
        $masked = str_repeat('*', strlen($data) - $showLast);

        return $masked . $visible;
    }

    /**
     * Mask email address
     */
    public static function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        if (strlen($local) <= 2) {
            $local = str_repeat('*', strlen($local));
        } else {
            $local = substr($local, 0, 2) . str_repeat('*', strlen($local) - 2);
        }

        return $local . '@' . $domain;
    }

    /**
     * Mask phone number
     */
    public static function maskPhone(string $phone): string
    {
        // Remove non-digits
        $clean = preg_replace('/\D/', '', $phone);

        if (strlen($clean) < 4) {
            return str_repeat('*', strlen($clean));
        }

        return str_repeat('*', strlen($clean) - 4) . substr($clean, -4);
    }

    /**
     * Encrypt entire array (for database storage)
     */
    public static function encryptArray(array $data): string
    {
        return Crypt::encryptString(json_encode($data));
    }

    /**
     * Decrypt entire array
     */
    public static function decryptArray(string $encrypted): array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            \Log::error('Array decryption failed: ' . $e->getMessage());
            return [];
        }
    }
}
