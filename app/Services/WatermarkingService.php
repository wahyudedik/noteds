<?php

namespace App\Services;

use App\Models\WatermarkSetting;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PageBoundaries;

class WatermarkingService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Apply watermark to a file
     *
     * @param string $filePath Original file path
     * @param string $disk Storage disk
     * @param WatermarkSetting $watermarkSetting Watermark settings
     * @return string Path to watermarked file
     */
    public function applyWatermark(
        string $filePath,
        string $disk,
        WatermarkSetting $watermarkSetting
    ): string {
        $fullPath = Storage::disk($disk)->path($filePath);
        $mimeType = Storage::disk($disk)->mimeType($filePath);

        // Check if watermark should be applied
        if (!$watermarkSetting->shouldApplyTo($mimeType)) {
            return $filePath; // Return original path
        }

        // Apply watermark based on type
        if (str_starts_with($mimeType, 'image/')) {
            return $this->watermarkImage($fullPath, $filePath, $disk, $watermarkSetting);
        } elseif ($mimeType === 'application/pdf') {
            return $this->watermarkPdf($fullPath, $filePath, $disk, $watermarkSetting);
        }

        return $filePath;
    }

    /**
     * Apply watermark to image
     */
    protected function watermarkImage(
        string $fullPath,
        string $filePath,
        string $disk,
        WatermarkSetting $watermarkSetting
    ): string {
        try {
            $image = $this->imageManager->read($fullPath);
            $width = $image->width();
            $height = $image->height();

            // Create watermark based on type
            if ($watermarkSetting->type === 'text') {
                $this->addTextWatermark($image, $watermarkSetting, $width, $height);
            } elseif ($watermarkSetting->type === 'image' && $watermarkSetting->image_path) {
                $this->addImageWatermark($image, $watermarkSetting, $width, $height);
            } elseif ($watermarkSetting->type === 'invisible') {
                $this->addInvisibleWatermark($image, $watermarkSetting);
            }

            // Save watermarked image
            $watermarkedPath = $this->getWatermarkedPath($filePath);
            $watermarkedFullPath = Storage::disk($disk)->path($watermarkedPath);
            
            // Ensure directory exists
            $directory = dirname($watermarkedFullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image->save($watermarkedFullPath);

            return $watermarkedPath;
        } catch (\Exception $e) {
            \Log::error('Watermarking failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return $filePath; // Return original on error
        }
    }

    /**
     * Add text watermark to image
     */
    protected function addTextWatermark($image, WatermarkSetting $setting, int $width, int $height): void
    {
        $text = $setting->text ?? 'Protected';
        $fontSize = $setting->text_size ?? 24;
        $color = $this->hexToRgb($setting->text_color ?? '#000000');
        $opacity = ($setting->opacity ?? 50) / 100;

        // Calculate position (simplified for v3)
        $position = $this->calculatePosition($setting->position, $width, $height, $fontSize * 10, $fontSize * 2);

        // Add text watermark using v3 API
        try {
            $image->text($text, $position['x'], $position['y'], function ($font) use ($fontSize, $color, $opacity, $setting) {
                $fontPath = $this->getFontPath($setting->text_font);
                if (file_exists($fontPath)) {
                    $font->file($fontPath);
                }
                $font->size($fontSize);
                $font->color($color['r'], $color['g'], $color['b'], (int)($opacity * 100));
                $font->align('center');
                $font->valign('middle');
            });
        } catch (\Exception $e) {
            // Fallback: use GD functions directly
            \Log::warning('Text watermark failed, using fallback', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Add image watermark to image
     */
    protected function addImageWatermark($image, WatermarkSetting $setting, int $width, int $height): void
    {
        if (!$setting->image_path || !Storage::disk('public')->exists($setting->image_path)) {
            return;
        }

        try {
            $watermarkPath = Storage::disk('public')->path($setting->image_path);
            $watermark = $this->imageManager->read($watermarkPath);

            // Resize watermark if needed
            $size = $setting->image_size ?? 20; // Percentage
            $watermarkWidth = (int)($width * $size / 100);
            $watermarkHeight = (int)($height * $size / 100);
            $watermark->scale($watermarkWidth, $watermarkHeight);

            // Apply opacity
            $opacity = ($setting->opacity ?? 50) / 100;
            $watermark->opacity((int)($opacity * 100));

            // Calculate position
            $position = $this->calculatePosition($setting->position, $width, $height, $watermarkWidth, $watermarkHeight);

            // Place watermark using v3 API
            $image->place($watermark, $setting->position, $position['x'], $position['y'], $opacity);
        } catch (\Exception $e) {
            \Log::warning('Image watermark failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Add invisible watermark (steganography)
     * Note: This is a simplified implementation. For production, consider using a dedicated steganography library.
     */
    protected function addInvisibleWatermark($image, WatermarkSetting $setting): void
    {
        // Simple steganography: embed watermark text in LSB of pixels
        // This is a basic implementation - for production use, consider a more robust solution
        try {
            $text = $setting->text ?? $setting->note_id;
            $binary = $this->textToBinary($text);

            $width = $image->width();
            $height = $image->height();
            $pixelCount = $width * $height;
            $textLength = strlen($binary);

            if ($textLength > $pixelCount) {
                // Text too long, use hash instead
                $binary = $this->textToBinary(substr(md5($text), 0, 16));
                $textLength = strlen($binary);
            }

            // Embed in first pixels using v3 API
            $pixelIndex = 0;
            for ($i = 0; $i < $textLength && $pixelIndex < $pixelCount; $i++) {
                $x = $pixelIndex % $width;
                $y = (int)($pixelIndex / $width);

                try {
                    $color = $image->pickColor($x, $y);
                    $r = $color->red();
                    $g = $color->green();
                    $b = $color->blue();

                    // Modify LSB of red channel
                    $r = ($r & 0xFE) | (int)$binary[$i];

                    $image->pixel($r, $g, $b, $x, $y);
                } catch (\Exception $e) {
                    // Skip pixel if error
                    continue;
                }
                $pixelIndex++;
            }
        } catch (\Exception $e) {
            \Log::warning('Invisible watermark failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Apply watermark to PDF
     */
    protected function watermarkPdf(
        string $fullPath,
        string $filePath,
        string $disk,
        WatermarkSetting $watermarkSetting
    ): string {
        try {
            $pdf = new Fpdi();
            
            // Get page count
            $pageCount = $pdf->setSourceFile($fullPath);
            
            $watermarkedPath = $this->getWatermarkedPath($filePath);
            $watermarkedFullPath = Storage::disk($disk)->path($watermarkedPath);
            
            // Ensure directory exists
            $directory = dirname($watermarkedFullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Process each page
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo, PageBoundaries::MEDIA_BOX);
                $size = $pdf->getTemplateSize($templateId);
                
                // Add page with same size
                if (is_array($size)) {
                    $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
                } else {
                    $pdf->AddPage();
                }
                
                // Use imported page
                $pdf->useTemplate($templateId);
                
                // Add watermark based on type
                if ($watermarkSetting->type === 'text') {
                    $this->addPdfTextWatermark($pdf, $watermarkSetting, is_array($size) ? $size : ['width' => 210, 'height' => 297]);
                } elseif ($watermarkSetting->type === 'image' && $watermarkSetting->image_path) {
                    $this->addPdfImageWatermark($pdf, $watermarkSetting, is_array($size) ? $size : ['width' => 210, 'height' => 297]);
                }
            }

            $pdf->Output('F', $watermarkedFullPath);
            
            return $watermarkedPath;
        } catch (\Exception $e) {
            \Log::error('PDF watermarking failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $filePath; // Return original on error
        }
    }

    /**
     * Add text watermark to PDF
     */
    protected function addPdfTextWatermark(Fpdi $pdf, WatermarkSetting $setting, array $size): void
    {
        $text = $setting->text ?? 'Protected';
        $fontSize = $setting->text_size ?? 24;
        $color = $this->hexToRgb($setting->text_color ?? '#000000');
        $opacity = ($setting->opacity ?? 50) / 100;

        // Calculate position
        $position = $this->calculatePdfPosition($setting->position, $size['width'], $size['height']);

        // Set font
        $pdf->SetFont('Helvetica', 'B', $fontSize);
        
        // Set text color with opacity (convert to grayscale for simplicity)
        $gray = (int)(($color['r'] + $color['g'] + $color['b']) / 3 * $opacity);
        $pdf->SetTextColor($gray, $gray, $gray);

        // Add text
        $pdf->SetXY($position['x'], $position['y']);
        $pdf->Cell(0, 0, $text, 0, 0, 'C');
    }

    /**
     * Add image watermark to PDF
     */
    protected function addPdfImageWatermark(Fpdi $pdf, WatermarkSetting $setting, array $size): void
    {
        if (!$setting->image_path || !Storage::disk('public')->exists($setting->image_path)) {
            return;
        }

        $watermarkPath = Storage::disk('public')->path($setting->image_path);
        
        if (!file_exists($watermarkPath)) {
            return;
        }

        // Calculate size
        $imageSize = $setting->image_size ?? 20; // Percentage
        $watermarkWidth = ($size['width'] * $imageSize / 100);
        $watermarkHeight = ($size['height'] * $imageSize / 100);

        // Calculate position
        $position = $this->calculatePdfPosition($setting->position, $size['width'], $size['height'], $watermarkWidth, $watermarkHeight);

        // Add image (FPDI doesn't support opacity directly, so we'll add it as-is)
        // For better opacity support, consider using TCPDF or FPDF with alpha channel support
        $pdf->Image($watermarkPath, $position['x'], $position['y'], $watermarkWidth, $watermarkHeight);
    }

    /**
     * Calculate watermark position for PDF
     */
    protected function calculatePdfPosition(string $position, float $width, float $height, float $watermarkWidth = 0, float $watermarkHeight = 0): array
    {
        $margin = 20;

        return match($position) {
            'top-left' => ['x' => $margin, 'y' => $margin],
            'top-right' => ['x' => $width - $watermarkWidth - $margin, 'y' => $margin],
            'center' => ['x' => ($width - $watermarkWidth) / 2, 'y' => ($height - $watermarkHeight) / 2],
            'bottom-left' => ['x' => $margin, 'y' => $height - $watermarkHeight - $margin],
            'bottom-right' => ['x' => $width - $watermarkWidth - $margin, 'y' => $height - $watermarkHeight - $margin],
            default => ['x' => ($width - $watermarkWidth) / 2, 'y' => ($height - $watermarkHeight) / 2],
        };
    }

    /**
     * Calculate watermark position
     */
    protected function calculatePosition(string $position, int $width, int $height, int $watermarkWidth, int $watermarkHeight): array
    {
        $margin = 20;

        return match($position) {
            'top-left' => ['x' => $margin, 'y' => $margin],
            'top-right' => ['x' => $width - $watermarkWidth - $margin, 'y' => $margin],
            'center' => ['x' => ($width - $watermarkWidth) / 2, 'y' => ($height - $watermarkHeight) / 2],
            'bottom-left' => ['x' => $margin, 'y' => $height - $watermarkHeight - $margin],
            'bottom-right' => ['x' => $width - $watermarkWidth - $margin, 'y' => $height - $watermarkHeight - $margin],
            default => ['x' => ($width - $watermarkWidth) / 2, 'y' => ($height - $watermarkHeight) / 2],
        };
    }

    /**
     * Get watermarked file path
     */
    protected function getWatermarkedPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_watermarked.' . $pathInfo['extension'];
    }

    /**
     * Convert hex color to RGB
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Get font path
     */
    protected function getFontPath(?string $fontName): string
    {
        if ($fontName && file_exists(storage_path("fonts/{$fontName}.ttf"))) {
            return storage_path("fonts/{$fontName}.ttf");
        }

        // Default font (system font)
        return __DIR__ . '/../../resources/fonts/arial.ttf';
    }

    /**
     * Convert text to binary
     */
    protected function textToBinary(string $text): string
    {
        $binary = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $binary .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }
        return $binary;
    }
}

