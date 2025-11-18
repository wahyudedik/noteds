<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageProcessingService
{
    /**
     * Process uploaded image and generate multiple sizes
     * 
     * Note: This is a basic implementation. For production, install intervention/image:
     * composer require intervention/image
     * 
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $filename
     * @return array Returns array with paths: ['original' => ..., 'thumbnail' => ..., 'medium' => ..., 'large' => ...]
     */
    public function processImage(UploadedFile $file, string $directory = 'images', ?string $filename = null): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $filename ?? Str::uuid() . '_' . time();
        $basePath = $directory . '/' . $filename;

        // Ensure directory exists
        Storage::disk('public')->makeDirectory($directory);

        // Save original
        $originalPath = $basePath . '.' . $extension;
        Storage::disk('public')->putFileAs($directory, $file, $filename . '.' . $extension);
        $paths = ['original' => $originalPath];

        // For now, return original path for all sizes
        // When intervention/image is installed, uncomment the code below
        /*
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $imageManager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            
            $image = $imageManager->read($file->getRealPath());
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            $sizes = [
                'thumbnail' => ['width' => 300, 'height' => 300],
                'medium' => ['width' => 600, 'height' => 600],
                'large' => ['width' => 1200, 'height' => 1200],
            ];

            foreach ($sizes as $sizeName => $dimensions) {
                $resized = clone $image;
                $resized->scale(
                    width: min($dimensions['width'], $originalWidth),
                    height: min($dimensions['height'], $originalHeight)
                );

                $sizePath = $basePath . '_' . $sizeName . '.' . $extension;
                Storage::disk('public')->put($sizePath, $resized->encode($extension)->toString());
                $paths[$sizeName] = $sizePath;
            }
        } else {
            // Fallback: use original for all sizes
            $paths['thumbnail'] = $originalPath;
            $paths['medium'] = $originalPath;
            $paths['large'] = $originalPath;
        }
        */

        // Fallback: use original for all sizes until intervention/image is installed
        $paths['thumbnail'] = $originalPath;
        $paths['medium'] = $originalPath;
        $paths['large'] = $originalPath;

        return $paths;
    }

    /**
     * Process thumbnail for note
     */
    public function processNoteThumbnail(UploadedFile $file, string $noteId): array
    {
        return $this->processImage($file, 'notes/' . $noteId . '/thumbnails');
    }

    /**
     * Get image URL with size variant
     */
    public function getImageUrl(string $path, string $size = 'original'): string
    {
        if ($size === 'original') {
            return Storage::url($path);
        }

        $pathInfo = pathinfo($path);
        $sizePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . ($pathInfo['extension'] ?? 'jpg');

        // Check if size variant exists, fallback to original
        if (Storage::disk('public')->exists($sizePath)) {
            return Storage::url($sizePath);
        }

        return Storage::url($path);
    }

    /**
     * Delete image and all its variants
     */
    public function deleteImage(string $path): void
    {
        $pathInfo = pathinfo($path);
        $basePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';

        $variants = [
            $basePath . '.' . $extension, // original
            $basePath . '_thumbnail.' . $extension,
            $basePath . '_medium.' . $extension,
            $basePath . '_large.' . $extension,
        ];

        foreach ($variants as $variant) {
            if (Storage::disk('public')->exists($variant)) {
                Storage::disk('public')->delete($variant);
            }
        }
    }
}

