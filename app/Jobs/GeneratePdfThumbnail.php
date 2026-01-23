<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $pdfPath, // storage path relative to public disk
        public string $thumbPath // desired thumbnail path relative to public disk
    ) {}

    public function handle(): void
    {
        if (!config('comments.pdf_thumbnails')) {
            return;
        }
        $disk = Storage::disk('public');
        if (!$disk->exists($this->pdfPath)) {
            return;
        }
        // Use Imagick if available
        if (extension_loaded('imagick')) {
            try {
                $fullPdf = $disk->path($this->pdfPath);
                $im = new \Imagick();
                $im->setResolution(150, 150);
                $im->readImage($fullPdf.'[0]');
                $im->setImageFormat('png');
                $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $im->setImageCompressionQuality(85);
                $im->resizeImage(512, 0, \Imagick::FILTER_LANCZOS, 1, true);
                $disk->put($this->thumbPath, $im->getImageBlob());
                $im->clear();
                $im->destroy();
            } catch (\Throwable $e) {
                // swallow errors to keep queue healthy
            }
        }
        // If Imagick not available, skip gracefully (manual upload or future job can provide thumbnails)
    }
}
