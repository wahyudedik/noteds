<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WithdrawalProofService
{
    /**
     * Store transfer proof images.
     *
     * @param array $files
     * @param string $withdrawalId
     * @param string $type 'approve' or 'complete'
     * @return array Array of stored file paths
     */
    public function storeProofImages(array $files, string $withdrawalId, string $type = 'approve'): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->storeAs(
                    "withdrawal-proofs/{$withdrawalId}/{$type}",
                    Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension(),
                    'public'
                );
                
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        
        return $paths;
    }

    /**
     * Delete transfer proof images.
     *
     * @param array $paths
     * @return void
     */
    public function deleteProofImages(array $paths): void
    {
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Get URL for a proof image.
     *
     * @param string $path
     * @return string|null
     */
    public function getProofUrl(string $path): ?string
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        
        return null;
    }

    /**
     * Get URLs for multiple proof images.
     *
     * @param array $paths
     * @return array
     */
    public function getProofUrls(array $paths): array
    {
        $urls = [];
        
        foreach ($paths as $path) {
            $url = $this->getProofUrl($path);
            if ($url) {
                $urls[] = [
                    'path' => $path,
                    'url' => $url,
                ];
            }
        }
        
        return $urls;
    }

    /**
     * Validate proof images.
     *
     * @param array $files
     * @param int $maxFiles
     * @param int $maxSizeKB
     * @return array Array of validation errors
     */
    public function validateProofImages(array $files, int $maxFiles = 5, int $maxSizeKB = 5120): array
    {
        $errors = [];
        
        if (count($files) > $maxFiles) {
            $errors[] = "Maximum {$maxFiles} images allowed.";
        }
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                // Validate file type
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    $errors[] = "File {$file->getClientOriginalName()} must be an image (JPEG, PNG, GIF, or WebP).";
                }
                
                // Validate file size
                $fileSizeKB = $file->getSize() / 1024;
                if ($fileSizeKB > $maxSizeKB) {
                    $errors[] = "File {$file->getClientOriginalName()} exceeds maximum size of {$maxSizeKB}KB.";
                }
            }
        }
        
        return $errors;
    }
}

