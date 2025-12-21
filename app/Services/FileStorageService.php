<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Upload product file.
     */
    public function uploadProductFile(UploadedFile $file, Product $product): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($product->name) . '-' . time() . '.' . $extension;
        $path = $file->storeAs('products/' . $product->user_id, $filename, 'products');

        return $path;
    }

    /**
     * Generate secure download link.
     */
    public function generateDownloadLink(Product $product, string $userId, string $orderId): string
    {
        // For local storage, return the route directly
        // In production, you might want to use signed URLs
        return route('marketplace.products.download', $product->id);
    }

    /**
     * Delete product file.
     */
    public function deleteProductFile(Product $product): bool
    {
        if ($product->file_download && Storage::disk('products')->exists($product->file_download)) {
            return Storage::disk('products')->delete($product->file_download);
        }

        return true;
    }
}

