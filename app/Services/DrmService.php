<?php

namespace App\Services;

use App\Models\DrmSetting;
use App\Models\DrmAccessLog;
use App\Models\DrmLicenseKey;
use App\Models\Note;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DrmService
{
    /**
     * Check if user can access a file with DRM protection
     *
     * @param Note $note
     * @param User $user
     * @param string $filePath
     * @param string|null $licenseKey
     * @return array ['allowed' => bool, 'message' => string, 'expires_at' => Carbon|null]
     */
    public function checkAccess(Note $note, User $user, string $filePath, ?string $licenseKey = null): array
    {
        $drmSetting = $note->drmSetting;

        // No DRM protection
        if (!$drmSetting || !$drmSetting->enabled) {
            return ['allowed' => true, 'message' => null, 'expires_at' => null];
        }

        // Check if user owns the note
        if ($note->user_id === $user->id) {
            return ['allowed' => true, 'message' => null, 'expires_at' => null];
        }

        // Check if user has purchased the note
        $transaction = Transaction::where('buyer_id', $user->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();

        if (!$transaction) {
            return ['allowed' => false, 'message' => 'You must purchase this note to access DRM-protected files.', 'expires_at' => null];
        }

        // Check license key if enabled
        if ($drmSetting->license_key_enabled) {
            if (!$licenseKey) {
                return ['allowed' => false, 'message' => 'License key required to access this file.', 'expires_at' => null];
            }

            $license = DrmLicenseKey::where('note_id', $note->id)
                ->where('user_id', $user->id)
                ->where('license_key', $licenseKey)
                ->first();

            if (!$license || !$license->isValid()) {
                return ['allowed' => false, 'message' => 'Invalid or expired license key.', 'expires_at' => null];
            }
        }

        // Check time-limited access
        if ($drmSetting->time_limited_access && $drmSetting->access_duration_days) {
            $purchaseDate = $transaction->created_at;
            $expiresAt = $purchaseDate->copy()->addDays($drmSetting->access_duration_days);

            if ($expiresAt->isPast()) {
                return ['allowed' => false, 'message' => 'Your access to this file has expired.', 'expires_at' => $expiresAt];
            }

            return ['allowed' => true, 'message' => null, 'expires_at' => $expiresAt];
        }

        // Check device limit
        if ($drmSetting->device_limit_enabled) {
            $deviceId = $this->getDeviceId($user, $note);
            $deviceCount = DrmAccessLog::where('note_id', $note->id)
                ->where('user_id', $user->id)
                ->where('device_id', $deviceId)
                ->distinct('device_id')
                ->count('device_id');

            if ($deviceCount >= $drmSetting->max_devices) {
                return ['allowed' => false, 'message' => "Maximum device limit ({$drmSetting->max_devices}) reached.", 'expires_at' => null];
            }
        }

        return ['allowed' => true, 'message' => null, 'expires_at' => null];
    }

    /**
     * Log file access
     */
    public function logAccess(
        Note $note,
        User $user,
        string $filePath,
        string $action = 'download',
        ?string $licenseKey = null
    ): DrmAccessLog {
        $transaction = Transaction::where('buyer_id', $user->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();

        $deviceId = $this->getDeviceId($user, $note);
        $deviceFingerprint = $this->getDeviceFingerprint();

        $drmSetting = $note->drmSetting;
        $expiresAt = null;

        if ($drmSetting && $drmSetting->time_limited_access && $drmSetting->access_duration_days && $transaction) {
            $expiresAt = $transaction->created_at->copy()->addDays($drmSetting->access_duration_days);
        }

        return DrmAccessLog::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'transaction_id' => $transaction?->id,
            'device_id' => $deviceId,
            'device_fingerprint' => $deviceFingerprint,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => $action,
            'file_path' => $filePath,
            'license_key' => $licenseKey,
            'accessed_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Generate license key for user
     */
    public function generateLicenseKey(
        Note $note,
        User $user,
        ?Transaction $transaction = null,
        string $keyType = 'per_user'
    ): DrmLicenseKey {
        $drmSetting = $note->drmSetting;

        if (!$drmSetting || !$drmSetting->license_key_enabled) {
            throw new \Exception('License keys are not enabled for this note.');
        }

        $deviceId = null;
        if ($keyType === 'per_device') {
            $deviceId = $this->getDeviceId($user, $note);
        }

        $licenseKey = $this->generateUniqueLicenseKey($note->id, $user->id);

        $expiresAt = null;
        if ($drmSetting->time_limited_access && $drmSetting->access_duration_days && $transaction) {
            $expiresAt = $transaction->created_at->copy()->addDays($drmSetting->access_duration_days);
        }

        return DrmLicenseKey::create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'transaction_id' => $transaction?->id,
            'license_key' => $licenseKey,
            'key_type' => $keyType,
            'device_id' => $deviceId,
            'is_active' => true,
            'download_count' => 0,
            'max_downloads' => $keyType === 'per_download' ? ($drmSetting->metadata['max_downloads'] ?? 5) : null,
            'issued_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Encrypt file
     */
    public function encryptFile(string $filePath, string $disk): string
    {
        $fullPath = Storage::disk($disk)->path($filePath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = file_get_contents($fullPath);
        $encrypted = Crypt::encrypt($content);

        $encryptedPath = $this->getEncryptedPath($filePath);
        $encryptedFullPath = Storage::disk($disk)->path($encryptedPath);

        // Ensure directory exists
        $directory = dirname($encryptedFullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($encryptedFullPath, $encrypted);

        return $encryptedPath;
    }

    /**
     * Decrypt file
     */
    public function decryptFile(string $encryptedPath, string $disk): string
    {
        $fullPath = Storage::disk($disk)->path($encryptedPath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Encrypted file not found: {$encryptedPath}");
        }

        $encrypted = file_get_contents($fullPath);
        $decrypted = Crypt::decrypt($encrypted);

        $tempPath = storage_path('app/temp/' . Str::uuid() . '.tmp');
        
        // Ensure temp directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        file_put_contents($tempPath, $decrypted);

        return $tempPath;
    }

    /**
     * Get device ID (hash of user ID + IP + user agent)
     */
    protected function getDeviceId(User $user, Note $note): string
    {
        $data = $user->id . $note->id . request()->ip() . request()->userAgent();
        return hash('sha256', $data);
    }

    /**
     * Get device fingerprint (more detailed)
     */
    protected function getDeviceFingerprint(): string
    {
        $data = [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'accept_language' => request()->header('Accept-Language'),
            'accept_encoding' => request()->header('Accept-Encoding'),
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Generate unique license key
     */
    protected function generateUniqueLicenseKey(string $noteId, string $userId): string
    {
        do {
            $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (DrmLicenseKey::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Get encrypted file path
     */
    protected function getEncryptedPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_encrypted.' . $pathInfo['extension'] . '.enc';
    }
}

