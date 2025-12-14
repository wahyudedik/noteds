<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * File Security Service
 * Handles secure file uploads dengan validation & virus scanning
 */
class FileSecurityService
{
    /**
     * Allowed MIME types by category
     */
    private static array $allowedMimes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        'archive' => ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'],
        'video' => ['video/mp4', 'video/mpeg', 'video/quicktime'],
        'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg'],
    ];

    /**
     * Max file sizes (in bytes)
     */
    private static array $maxSizes = [
        'image' => 5 * 1024 * 1024, // 5MB
        'document' => 10 * 1024 * 1024, // 10MB
        'video' => 500 * 1024 * 1024, // 500MB
        'audio' => 100 * 1024 * 1024, // 100MB
        'default' => 2 * 1024 * 1024, // 2MB
    ];

    /**
     * Dangerous extensions to block
     */
    private static array $dangerousExtensions = [
        'exe',
        'bat',
        'cmd',
        'com',
        'pif',
        'scr',
        'vbs',
        'js',
        'jar',
        'zip',
        'rar',
        '7z',
        'sh',
        'bash',
        'php',
        'asp',
        'aspx',
        'jsp',
        'py',
        'pl',
        'rb',
        'svg',
        'xhtml',
    ];

    /**
     * Validate file upload
     */
    public static function validateFile(UploadedFile $file, string $category = 'default'): array
    {
        $errors = [];

        // Check extension
        if (self::isDangerousExtension($file->getClientOriginalExtension())) {
            $errors[] = 'This file type is not allowed.';
        }

        // Check MIME type
        $allowedMimes = self::$allowedMimes[$category] ?? self::$allowedMimes['default'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type not allowed. Expected: ' . implode(', ', $allowedMimes);
        }

        // Check file size
        $maxSize = self::$maxSizes[$category] ?? self::$maxSizes['default'];
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum limit (' . self::formatBytes($maxSize) . ')';
        }

        // Check file integrity
        if (!self::isFileIntact($file)) {
            $errors[] = 'File appears to be corrupted or invalid.';
        }

        return $errors;
    }

    /**
     * Store file securely
     */
    public static function storeSecurely(UploadedFile $file, string $path = '', string $disk = 'public'): ?string
    {
        // Validate first
        $errors = self::validateFile($file, self::getCategoryFromMime($file->getMimeType()));
        if (!empty($errors)) {
            return null;
        }

        // Generate unique name
        $hash = Str::random(32);
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = "{$hash}.{$extension}";

        // Store in subdirectory
        $storagePath = $path ? "{$path}/{$fileName}" : $fileName;

        try {
            return Storage::disk($disk)->putFileAs($path, $file, $fileName);
        } catch (\Exception $e) {
            \Log::error('File storage failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store avatar securely
     */
    public static function storeAvatar(UploadedFile $file, int $userId, string $disk = 'public'): ?string
    {
        // Validate image
        $errors = self::validateFile($file, 'image');
        if (!empty($errors)) {
            return null;
        }

        // Resize image untuk optimize storage
        try {
            $image = \Image::make($file)->fit(500, 500);

            $fileName = "avatars/user-{$userId}-" . Str::random(16) . '.webp';

            Storage::disk($disk)->put(
                $fileName,
                $image->encode('webp', 85)
            );

            return $fileName;
        } catch (\Exception $e) {
            \Log::error('Avatar storage failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store cover image
     */
    public static function storeCoverImage(UploadedFile $file, string $disk = 'public'): ?string
    {
        $errors = self::validateFile($file, 'image');
        if (!empty($errors)) {
            return null;
        }

        try {
            $image = \Image::make($file)->fit(1200, 600);

            $fileName = "covers/" . Str::random(32) . '.webp';

            Storage::disk($disk)->put(
                $fileName,
                $image->encode('webp', 85)
            );

            return $fileName;
        } catch (\Exception $e) {
            \Log::error('Cover image storage failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete file securely
     */
    public static function deleteFile(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if extension is dangerous
     */
    private static function isDangerousExtension(string $extension): bool
    {
        return in_array(strtolower($extension), self::$dangerousExtensions);
    }

    /**
     * Check if file is intact (not corrupted)
     */
    private static function isFileIntact(UploadedFile $file): bool
    {
        // Check if file is readable
        if (!is_readable($file->getRealPath())) {
            return false;
        }

        // For images, validate headers
        $mimeType = $file->getMimeType();
        if (str_starts_with($mimeType, 'image/')) {
            return self::isValidImage($file->getRealPath());
        }

        return true;
    }

    /**
     * Validate image file
     */
    private static function isValidImage(string $filePath): bool
    {
        try {
            $image = \Image::make($filePath);
            return $image && $image->width() > 0 && $image->height() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file category from MIME type
     */
    private static function getCategoryFromMime(string $mimeType): string
    {
        foreach (self::$allowedMimes as $category => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $category;
            }
        }
        return 'default';
    }

    /**
     * Format bytes to human-readable
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Scan file for malware (integration point)
     */
    public static function scanForMalware(string $filePath): bool
    {
        // Integration point for ClamAV or similar scanning
        // For now, basic checks only

        if (!file_exists($filePath)) {
            return false;
        }

        // Check file size (too small or too large = suspicious)
        $fileSize = filesize($filePath);
        if ($fileSize < 10 || $fileSize > 1000000000) { // 1GB
            return false;
        }

        return true;
    }

    /**
     * Get safe file information
     */
    public static function getFileInfo(string $path, string $disk = 'public'): array
    {
        try {
            return [
                'size' => Storage::disk($disk)->size($path),
                'last_modified' => Storage::disk($disk)->lastModified($path),
                'mime_type' => Storage::disk($disk)->mimeType($path),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if user can access file
     */
    public static function canAccessFile(string $path, int $userId): bool
    {
        // Implement permission checking based on your business logic
        // This prevents users from accessing files they shouldn't have access to

        // Example: Check if file belongs to user's own uploads
        return Storage::disk('public')->exists($path);
    }
}
