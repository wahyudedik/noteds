<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessFileUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public string $disk = 'public',
        public ?array $options = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!Storage::disk($this->disk)->exists($this->filePath)) {
                Log::warning('File not found for processing', [
                    'file_path' => $this->filePath,
                    'disk' => $this->disk,
                ]);
                return;
            }

            // Get file info
            $fileSize = Storage::disk($this->disk)->size($this->filePath);
            $mimeType = Storage::disk($this->disk)->mimeType($this->filePath);

            Log::info('File processed', [
                'file_path' => $this->filePath,
                'disk' => $this->disk,
                'size' => $fileSize,
                'mime_type' => $mimeType,
            ]);

            // Additional processing can be added here:
            // - Image optimization
            // - Video transcoding
            // - PDF processing
            // - Virus scanning
            // - Thumbnail generation

        } catch (\Exception $e) {
            Log::error('File processing job failed', [
                'file_path' => $this->filePath,
                'disk' => $this->disk,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('File processing job failed permanently', [
            'file_path' => $this->filePath,
            'disk' => $this->disk,
            'error' => $exception->getMessage(),
        ]);
    }
}

