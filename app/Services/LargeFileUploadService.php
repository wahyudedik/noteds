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
     * Large file threshold (40MB)
     */
    const LARGE_FILE_THRESHOLD = 41943040; // 40MB in bytes

    /**
     * Chunk size for large files (5MB per chunk)
     */
    const CHUNK_SIZE = 5242880; // 5MB

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
        $fileSize = $file->getSize();
        $isLargeFile = $fileSize >= self::LARGE_FILE_THRESHOLD;

        // For large files, use special handling
        if ($isLargeFile) {
            return $this->handleLargeFile($file, $userId, $progressCallback);
        }

        // For regular files, use standard upload
        return $this->handleRegularFile($file, $userId);
    }

    /**
     * Handle regular file upload (under 40MB)
     *
     * @param UploadedFile $file
     * @param string $userId
     * @return array
     */
    protected function handleRegularFile(UploadedFile $file, string $userId): array
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
            // Ensure private storage directory exists
            if (!Storage::disk('private')->exists('notes/' . $userId)) {
                Storage::disk('private')->makeDirectory('notes/' . $userId);
            }

            // Generate unique filename
            $filename = Str::uuid() . '_' . Str::slug($file->getClientOriginalName());
            $destinationPath = 'notes/' . $userId . '/' . $filename;

            // Use streaming for large files to avoid memory issues
            $fileSize = $file->getSize();
            $uploadedBytes = 0;

            // Increase PHP limits for large file upload
            $oldMaxExecutionTime = ini_get('max_execution_time');
            $oldMemoryLimit = ini_get('memory_limit');
            
            try {
                set_time_limit(600); // 10 minutes
                ini_set('memory_limit', '512M');
                
                // Use Laravel Storage's putStream with streaming for large files
                // This handles memory efficiently by streaming the file
                $stream = fopen($file->getRealPath(), 'rb');
                if (!$stream) {
                    throw new Exception('Failed to open source file for reading.');
                }

                // Use putStream which handles large files efficiently
                $success = Storage::disk('private')->putStream($destinationPath, $stream);
                
                if ($stream) {
                    fclose($stream);
                }
                
                if (!$success) {
                    throw new Exception('Failed to upload file to storage.');
                }

                // Simulate progress for callback (since putStream doesn't provide progress)
                if ($progressCallback) {
                    // Call progress at 25%, 50%, 75%, and 100%
                    $progressCallback(25, $fileSize * 0.25, $fileSize);
                    $progressCallback(50, $fileSize * 0.5, $fileSize);
                    $progressCallback(75, $fileSize * 0.75, $fileSize);
                    $progressCallback(100, $fileSize, $fileSize);
                }
            } finally {
                // Restore original PHP limits
                if ($oldMaxExecutionTime) {
                    set_time_limit($oldMaxExecutionTime);
                }
                if ($oldMemoryLimit) {
                    ini_set('memory_limit', $oldMemoryLimit);
                }
            }

            // Verify file was uploaded correctly
            if (!Storage::disk('private')->exists($destinationPath)) {
                throw new Exception('File upload verification failed.');
            }

            $uploadedSize = Storage::disk('private')->size($destinationPath);
            if ($uploadedSize !== $fileSize) {
                Storage::disk('private')->delete($destinationPath);
                throw new Exception('File size mismatch after upload.');
            }

            Log::info('Large file uploaded successfully', [
                'user_id' => $userId,
                'filename' => $file->getClientOriginalName(),
                'size' => $fileSize,
                'path' => $destinationPath,
            ]);

            return [
                'filename' => $file->getClientOriginalName(),
                'path' => $destinationPath,
                'size' => $fileSize,
                'mime' => $file->getMimeType(),
                'is_large' => true,
            ];

        } catch (Exception $e) {
            Log::error('Large file upload failed', [
                'user_id' => $userId,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            // Clean up partial upload if exists
            if (isset($destinationPath) && Storage::disk('private')->exists($destinationPath)) {
                Storage::disk('private')->delete($destinationPath);
            }

            throw $e;
        }
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
        $maxSize = $isPremium ? 104857600 : 5242880; // 100MB for premium, 5MB for basic
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

        // Check if file is too large for memory (warn but allow)
        if ($fileSize >= self::LARGE_FILE_THRESHOLD) {
            return [
                'valid' => true,
                'warning' => 'Large file detected. Upload may take longer. Please do not close this page.',
                'is_large' => true,
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

