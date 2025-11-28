<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class LargeFileUploadService
{
    /**
     * Maximum file size (10MB)
     */
    const MAX_FILE_SIZE = 10485760; // 10MB in bytes

    /**
     * Threshold for large file handling (40MB)
     */
    const LARGE_FILE_THRESHOLD = 41943040; // 40MB in bytes

    /**
     * Maximum files per note
     */
    const MAX_FILES_PER_NOTE = 10;

    /**
     * Handle large file upload with progress tracking and error handling
     *
     * @param UploadedFile $file
     * @param string $userId
     * @param callable|null $progressCallback
     * @return array
     * @throws Exception
     */
    public function handleLargeFileUpload(UploadedFile $file, string $userId, ?callable $progressCallback = null): array
    {
        // All files use standard upload (max 10MB)
        return $this->handleRegularFile($file, $userId);
    }

    /**
     * Handle regular file upload (under 40MB)
     *
     * @param UploadedFile $file
     * @param string $userId
     * @return array
     */
    public function handleRegularFile(UploadedFile $file, string $userId): array
    {
        // Ensure private storage directory exists
        if (!Storage::disk('private')->exists('notes/' . $userId)) {
            Storage::disk('private')->makeDirectory('notes/' . $userId);
        }

        // Generate unique filename
        $filename = Str::uuid() . '_' . Str::slug($file->getClientOriginalName());
        $path = Storage::disk('private')->putFileAs('notes/' . $userId, $file, $filename);

        return [
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'is_large' => false,
        ];
    }

    /**
     * Handle large file upload (40MB+) with streaming and progress tracking
     *
     * @param UploadedFile $file
     * @param string $userId
     * @param callable|null $progressCallback
     * @return array
     * @throws Exception
     */
    protected function handleLargeFile(UploadedFile $file, string $userId, ?callable $progressCallback = null): array
    {
        try {
            // Ensure memory limit is set before processing
            // This should already be set by the controller, but set it here as well for safety
            $currentMemoryLimit = ini_get('memory_limit');
            $currentMemoryLimitBytes = $this->convertToBytes($currentMemoryLimit);
            if ($currentMemoryLimitBytes < 512 * 1024 * 1024) { // Less than 512MB
                ini_set('memory_limit', '512M');
            }

            // Ensure private storage directory exists
            if (!Storage::disk('private')->exists('notes/' . $userId)) {
                Storage::disk('private')->makeDirectory('notes/' . $userId);
            }

            // Get file info BEFORE opening stream to minimize memory usage
            $originalFilename = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Generate unique filename
            $filename = Str::uuid() . '_' . Str::slug($originalFilename);

            // Increase PHP limits for large file upload
            set_time_limit(900); // 15 minutes for very large files
            ini_set('max_execution_time', '900');
            ini_set('max_input_time', '900');

            // Use Laravel Storage's putFileAs which handles large files efficiently
            // putFileAs uses move_uploaded_file internally which is more memory efficient
            // than reading the entire file into memory
            try {
                // Use putFileAs which is optimized for uploaded files
                // It uses move_uploaded_file internally, which is memory efficient
                $path = Storage::disk('private')->putFileAs('notes/' . $userId, $file, $filename);

                if (!$path) {
                    throw new Exception('Failed to upload file to storage.');
                }

                // Verify the file was uploaded correctly
                if (!Storage::disk('private')->exists($path)) {
                    throw new Exception('File upload verification failed: file does not exist.');
                }

                $uploadedSize = Storage::disk('private')->size($path);
                if ($uploadedSize !== $fileSize) {
                    Storage::disk('private')->delete($path);
                    throw new Exception("File size mismatch after upload. Expected: {$fileSize}, Got: {$uploadedSize}");
                }

                // Simulate progress for callback
                if ($progressCallback) {
                    $progressCallback(100, $fileSize, $fileSize);
                }
            } catch (\Exception $e) {
                // Clean up partial upload if exists
                if (isset($path) && Storage::disk('private')->exists($path)) {
                    Storage::disk('private')->delete($path);
                }
                throw $e;
            }

            Log::info('Large file uploaded successfully', [
                'user_id' => $userId,
                'filename' => $originalFilename,
                'size' => $fileSize,
                'path' => $path,
            ]);

            return [
                'filename' => $originalFilename,
                'path' => $path,
                'size' => $fileSize,
                'mime' => $mimeType,
                'is_large' => true,
            ];
        } catch (Exception $e) {
            Log::error('Large file upload failed', [
                'user_id' => $userId,
                'filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up partial upload if exists
            if (isset($path) && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }

            throw $e;
        }
    }

    /**
     * Convert memory limit string to bytes
     * 
     * @param string $memoryLimit
     * @return int
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Validate file before upload
     *
     * @param UploadedFile $file
     * @param bool $isPremium
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateFile(UploadedFile $file, bool $isPremium = false): array
    {
        $maxSize = self::MAX_FILE_SIZE; // 10MB for all users
        $fileSize = $file->getSize();

        // Check file size
        if ($fileSize > $maxSize) {
            $sizeInMB = round($fileSize / 1048576, 2);
            $maxSizeMB = round($maxSize / 1048576, 2);
            return [
                'valid' => false,
                'error' => "File size ({$sizeInMB}MB) exceeds maximum allowed size ({$maxSizeMB}MB).",
            ];
        }

        // Check MIME type
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            return [
                'valid' => false,
                'error' => "File type '{$mimeType}' is not allowed.",
            ];
        }

        return [
            'valid' => true,
            'is_large' => false,
        ];
    }

    /**
     * Get upload progress for a file (if using session-based tracking)
     *
     * @param string $uploadId
     * @return array|null
     */
    public function getUploadProgress(string $uploadId): ?array
    {
        $progressKey = "upload_progress_{$uploadId}";
        $progress = session($progressKey);

        return $progress ? [
            'progress' => $progress['progress'] ?? 0,
            'uploaded' => $progress['uploaded'] ?? 0,
            'total' => $progress['total'] ?? 0,
        ] : null;
    }

    /**
     * Set upload progress in session
     *
     * @param string $uploadId
     * @param float $progress
     * @param int $uploaded
     * @param int $total
     * @return void
     */
    public function setUploadProgress(string $uploadId, float $progress, int $uploaded, int $total): void
    {
        $progressKey = "upload_progress_{$uploadId}";
        session([
            $progressKey => [
                'progress' => $progress,
                'uploaded' => $uploaded,
                'total' => $total,
                'updated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
