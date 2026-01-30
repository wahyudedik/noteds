<?php

namespace App\Services;

class ImageOptimizer
{
    public function optimize(string $absolutePath, string $mime = null): bool
    {
        if (!file_exists($absolutePath)) return false;
        $mime = $mime ?: mime_content_type($absolutePath);
        try {
            if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $img = imagecreatefromjpeg($absolutePath);
                if (!$img) return false;
                imagejpeg($img, $absolutePath, 80);
                imagedestroy($img);
                return true;
            } elseif (str_contains($mime, 'png')) {
                $img = imagecreatefrompng($absolutePath);
                if (!$img) return false;
                imagepng($img, $absolutePath, 6);
                imagedestroy($img);
                return true;
            } elseif (str_contains($mime, 'webp')) {
                if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
                    $img = imagecreatefromwebp($absolutePath);
                    if (!$img) return false;
                    imagewebp($img, $absolutePath, 80);
                    imagedestroy($img);
                    return true;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Image optimize failed: ' . $e->getMessage());
        }
        return false;
    }

    public function convertToWebp(string $absolutePath, string $mime = null): ?string
    {
        if (!file_exists($absolutePath)) return null;
        $mime = $mime ?: mime_content_type($absolutePath);
        try {
            if (!function_exists('imagewebp')) return null;
            $img = null;
            if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $img = imagecreatefromjpeg($absolutePath);
            } elseif (str_contains($mime, 'png')) {
                $img = imagecreatefrompng($absolutePath);
            } elseif (str_contains($mime, 'gif')) {
                if (function_exists('imagecreatefromgif')) $img = imagecreatefromgif($absolutePath);
            } else {
                return null;
            }
            if (!$img) return null;
            $webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $absolutePath);
            imagewebp($img, $webpPath, 80);
            imagedestroy($img);
            return $webpPath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Image webp convert failed: ' . $e->getMessage());
            return null;
        }
    }
}
