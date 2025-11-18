<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoService
{
    /**
     * Maximum video duration in seconds (2 minutes).
     */
    const MAX_DURATION = 120;

    /**
     * Allowed video MIME types.
     */
    const ALLOWED_MIME_TYPES = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
    ];

    /**
     * Process and store video preview.
     */
    public function processVideoPreview(UploadedFile $file, string $path = 'videos/previews'): array
    {
        // Validate file
        $this->validateVideo($file);

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Store video
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Get video duration
        $duration = $this->getVideoDuration($storedPath);

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($storedPath, $path);

        return [
            'video_path' => $storedPath,
            'thumbnail_path' => $thumbnailPath,
            'duration' => $duration,
        ];
    }

    /**
     * Validate video file.
     */
    protected function validateVideo(UploadedFile $file): void
    {
        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException('Invalid video format. Allowed formats: MP4, WebM, OGG, QuickTime.');
        }

        // Check file size (max 100MB)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('Video file is too large. Maximum size is 100MB.');
        }

        // Check duration (will be validated after upload)
        // We'll check this in getVideoDuration
    }

    /**
     * Get video duration in seconds.
     */
    protected function getVideoDuration(string $videoPath): int
    {
        $fullPath = Storage::disk('public')->path($videoPath);

        // Try to use FFmpeg if available
        if ($this->hasFFmpeg()) {
            try {
                $duration = $this->getDurationWithFFmpeg($fullPath);
                if ($duration > self::MAX_DURATION) {
                    // Delete the uploaded video
                    Storage::disk('public')->delete($videoPath);
                    throw new \InvalidArgumentException('Video duration exceeds 2 minutes. Maximum duration is 2 minutes.');
                }
                return (int) $duration;
            } catch (\Exception $e) {
                Log::warning('FFmpeg duration check failed: ' . $e->getMessage());
            }
        }

        // Fallback: return 0 if we can't determine duration
        // In production, you might want to use a queue job to process this
        return 0;
    }

    /**
     * Check if FFmpeg is available.
     */
    protected function hasFFmpeg(): bool
    {
        $ffmpegPath = config('services.ffmpeg.path', 'ffmpeg');
        
        try {
            $output = [];
            $returnVar = 0;
            exec("$ffmpegPath -version 2>&1", $output, $returnVar);
            return $returnVar === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get video duration using FFmpeg.
     */
    protected function getDurationWithFFmpeg(string $videoPath): float
    {
        $ffmpegPath = config('services.ffmpeg.path', 'ffmpeg');
        $command = escapeshellarg($ffmpegPath) . " -i " . escapeshellarg($videoPath) . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";
        
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || empty($output)) {
            throw new \RuntimeException('Failed to get video duration');
        }

        $durationString = $output[0];
        return $this->parseDuration($durationString);
    }

    /**
     * Parse duration string (HH:MM:SS.microseconds) to seconds.
     */
    protected function parseDuration(string $duration): float
    {
        $parts = explode(':', $duration);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid duration format');
        }

        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        $seconds = (float) $parts[2];

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Generate thumbnail from video.
     */
    protected function generateThumbnail(string $videoPath, string $basePath): ?string
    {
        $fullVideoPath = Storage::disk('public')->path($videoPath);

        // Try to use FFmpeg if available
        if ($this->hasFFmpeg()) {
            try {
                return $this->generateThumbnailWithFFmpeg($fullVideoPath, $basePath);
            } catch (\Exception $e) {
                Log::warning('FFmpeg thumbnail generation failed: ' . $e->getMessage());
            }
        }

        // Fallback: return null (no thumbnail)
        // In production, you might want to use a queue job to process this
        return null;
    }

    /**
     * Generate thumbnail using FFmpeg.
     */
    protected function generateThumbnailWithFFmpeg(string $videoPath, string $basePath): string
    {
        $ffmpegPath = config('services.ffmpeg.path', 'ffmpeg');
        $thumbnailFilename = Str::uuid() . '.jpg';
        $thumbnailPath = $basePath . '/thumbnails/' . $thumbnailFilename;
        $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);

        // Ensure directory exists
        Storage::disk('public')->makeDirectory(dirname($thumbnailPath));

        // Extract frame at 1 second (or first frame if video is shorter)
        $command = escapeshellarg($ffmpegPath) . 
            " -i " . escapeshellarg($videoPath) . 
            " -ss 00:00:01 -vframes 1 -q:v 2 " . 
            escapeshellarg($fullThumbnailPath) . 
            " 2>&1";

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($fullThumbnailPath)) {
            throw new \RuntimeException('Failed to generate thumbnail');
        }

        return $thumbnailPath;
    }

    /**
     * Delete video and thumbnail.
     */
    public function deleteVideoPreview(string $videoPath, ?string $thumbnailPath = null): void
    {
        if ($videoPath) {
            Storage::disk('public')->delete($videoPath);
        }
        if ($thumbnailPath) {
            Storage::disk('public')->delete($thumbnailPath);
        }
    }
}

