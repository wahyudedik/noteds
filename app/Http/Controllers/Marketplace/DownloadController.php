<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Download;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __construct(
        private FileStorageService $fileStorageService
    ) {}

    public function download(Product $product)
    {
        $user = auth()->user();

        // Check if user has a paid order for this product
        $order = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('payment_status', 'paid')
            ->first();

        if (!$order) {
            abort(403, 'You must purchase this product to download it.');
        }

        if (!$product->file_download) {
            abort(404, 'Download file not available.');
        }

        // Record download
        Download::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'downloaded_at' => now(),
        ]);

        // Return file download
        if (!Storage::disk('products')->exists($product->file_download)) {
            abort(404, 'File not found');
        }
        
        return Storage::disk('products')->download($product->file_download);
    }
}
