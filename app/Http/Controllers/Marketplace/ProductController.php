<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductBundleRequest;
use App\Models\Product;
use App\Services\FileStorageService;
use App\Services\ProductComparisonService;
use App\Services\BundleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private FileStorageService $fileStorageService,
        private ProductComparisonService $comparisonService,
        private BundleService $bundleService
    ) {}

    public function index(Request $request)
    {
        $page = (int) ($request->get('page', 1));
        $search = (string) ($request->get('search', ''));
        $category = (string) ($request->get('category', ''));
        $version = (int) (\Illuminate\Support\Facades\Cache::get('mk:products:index:v', 1));
        $cacheKey = "mk:products:index:v{$version}:p{$page}:s:" . md5($search) . ":c:{$category}";
        $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 20, function () use ($request) {
            $query = Product::with('seller')->active();

            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            return $query->latest()->paginate(12);
        });

        return Inertia::render('Marketplace/Index', [
            'products' => $products,
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $product->increment('views_count');
        $product->load(['seller']);

        // Check if user has purchased this product (for review form)
        $hasPurchased = false;
        if ($request->user()) {
            $hasPurchased = \App\Models\Order::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->where('payment_status', 'paid')
                ->exists();
        }

        // Get paginated reviews
        $page = (int) ($request->get('page', 1));
        $pver = (int) (\Illuminate\Support\Facades\Cache::get("mk:product:{$product->id}:v", 1));
        $cacheKey = "mk:products:{$product->id}:v{$pver}:reviews:p{$page}";
        $reviews = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, function () use ($product) {
            return $product->reviews()
                ->with('user')
                ->latest()
                ->paginate(10);
        });
        $avgKey = "mk:products:{$product->id}:v{$pver}:avg_rating";
        $countKey = "mk:products:{$product->id}:v{$pver}:reviews_count";
        $averageRating = \Illuminate\Support\Facades\Cache::remember($avgKey, 60, fn() => $product->averageRating());
        $reviewsCount = \Illuminate\Support\Facades\Cache::remember($countKey, 60, fn() => $product->reviews()->count());

        return Inertia::render('Marketplace/Product/Show', [
            'product' => $product,
            'averageRating' => $averageRating,
            'reviewsCount' => $reviewsCount,
            'reviews' => $reviews,
            'hasPurchased' => $hasPurchased,
        ]);
    }

    /**
     * Generate shareable content (API).
     */
    public function shareContent(Product $product, Request $request): JsonResponse
    {
        $base = config('app.url');
        $platform = (string) $request->get('platform', 'generic');
        $utm = http_build_query([
            'utm_source' => $platform,
            'utm_medium' => 'social',
            'utm_campaign' => 'marketplace_share',
            'utm_product' => $product->id,
        ]);
        $shareUrl = "{$base}" . route('marketplace.products.show', $product->id, false) . "?{$utm}";
        $image = $product->image_url ?: ($product->image ? asset('storage/' . $product->image) : asset('/images/placeholder.png'));
        return response()->json([
            'title' => $product->name,
            'description' => mb_substr($product->description ?? '', 0, 160),
            'image' => $image,
            'price' => (float) $product->price,
            'currency' => 'IDR',
            'url' => $shareUrl,
        ]);
    }
    
    /**
     * Track product share (for analytics).
     */
    public function trackShare(Request $request, Product $product)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:whatsapp,facebook,twitter,linkedin,telegram,email,copy_link,instagram,tiktok,noteds_home',
        ]);

        // Optional: Track share analytics
        // You can log this to a product_shares table or analytics service
        
        return response()->json(['success' => true]);
    }

    public function create()
    {
        return Inertia::render('Marketplace/Product/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'file_download' => 'nullable|file|max:51200',
            'license_key' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0',
        ], [
            'file_download.max' => 'File terlalu besar. Ukuran maksimal: 50MB.',
            'file_download.file' => 'File yang diunggah harus berupa file yang valid.',
            'image.max' => 'Gambar terlalu besar. Ukuran maksimal: 2MB.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/images', 'public');
            $validated['image'] = $path;
            try {
                $optimizer = app(\App\Services\ImageOptimizer::class);
                $absolute = storage_path('app/public/' . $path);
                $optimizer->optimize($absolute, $request->file('image')->getMimeType());
                $webp = $optimizer->convertToWebp($absolute, $request->file('image')->getMimeType());
                if ($webp) {
                    $validated['image_webp'] = str_replace(storage_path('app/public/'), '', $webp);
                }
            } catch (\Throwable $e) {}
        }

        $product = Product::create($validated);
        try {
            \Illuminate\Support\Facades\Cache::put('mk:products:index:v', time(), 86400);
            \Illuminate\Support\Facades\Cache::put("mk:product:{$product->id}:v", time(), 86400);
        } catch (\Throwable $e) {}

        if ($request->hasFile('file_download')) {
            $filePath = $this->fileStorageService->uploadProductFile(
                $request->file('file_download'),
                $product
            );
            $product->update(['file_download' => $filePath]);
        }

        // Notify admin about new product
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyProductCreated($product);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send product created notification', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('marketplace.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return Inertia::render('Marketplace/Product/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'file_download' => 'nullable|file|max:51200',
            'license_key' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'file_download.max' => 'File terlalu besar. Ukuran maksimal: 50MB.',
            'file_download.file' => 'File yang diunggah harus berupa file yang valid.',
            'image.max' => 'Gambar terlalu besar. Ukuran maksimal: 2MB.',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products/images', 'public');
            $validated['image'] = $path;
            try {
                $optimizer = app(\App\Services\ImageOptimizer::class);
                $absolute = storage_path('app/public/' . $path);
                $optimizer->optimize($absolute, $request->file('image')->getMimeType());
                $webp = $optimizer->convertToWebp($absolute, $request->file('image')->getMimeType());
                if ($webp) {
                    $validated['image_webp'] = str_replace(storage_path('app/public/'), '', $webp);
                }
            } catch (\Throwable $e) {}
        }

        if ($request->hasFile('file_download')) {
            $this->fileStorageService->deleteProductFile($product);
            $filePath = $this->fileStorageService->uploadProductFile(
                $request->file('file_download'),
                $product
            );
            $validated['file_download'] = $filePath;
        }

        $product->update($validated);
        try {
            \Illuminate\Support\Facades\Cache::put('mk:products:index:v', time(), 86400);
            \Illuminate\Support\Facades\Cache::put("mk:product:{$product->id}:v", time(), 86400);
        } catch (\Throwable $e) {}

        return redirect()->route('marketplace.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $this->fileStorageService->deleteProductFile($product);

        $product->delete();
        try {
            \Illuminate\Support\Facades\Cache::put('mk:products:index:v', time(), 86400);
        } catch (\Throwable $e) {}

        return redirect()->route('marketplace.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function myProducts(Request $request)
    {
        $query = Product::where('user_id', auth()->id());

        // Filter by active/inactive
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(12);

        // Get stats
        $totalProducts = Product::where('user_id', auth()->id())->count();
        $totalSales = Product::where('user_id', auth()->id())->sum('sales_count');
        $totalEarnings = \App\Models\Order::whereHas('product', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->where('payment_status', 'paid')
        ->sum('total');

        return Inertia::render('Marketplace/Products/MyProducts', [
            'products' => $products,
            'filters' => $request->only(['status']),
            'stats' => [
                'total_products' => $totalProducts,
                'total_sales' => $totalSales,
                'total_earnings' => $totalEarnings,
            ],
        ]);
    }

    /**
     * Get product variants.
     */
    public function variants(Product $product)
    {
        $variants = $product->variants()
            ->with('seller')
            ->get()
            ->groupBy('variant_type');

        return response()->json([
            'product' => $product,
            'variants' => $variants,
        ]);
    }

    /**
     * Create a product bundle.
     */
    public function storeBundle(StoreProductBundleRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;
        $validated['is_bundle'] = true;

        $bundle = Product::create($validated);

        // Create bundle items
        $this->bundleService->createBundle($bundle, $request->items);

        return redirect()->route('marketplace.products.show', $bundle)
            ->with('success', 'Bundle created successfully.');
    }

    /**
     * Add product to comparison.
     */
    public function addToComparison(Request $request, Product $product): JsonResponse
    {
        $sessionId = $request->session()->getId();

        try {
            $this->comparisonService->addToComparison($sessionId, $product);
            return response()->json(['success' => true, 'message' => 'Product added to comparison']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove product from comparison.
     */
    public function removeFromComparison(Request $request, Product $product): JsonResponse
    {
        $sessionId = $request->session()->getId();

        try {
            $this->comparisonService->removeFromComparison($sessionId, $product);
            return response()->json(['success' => true, 'message' => 'Product removed from comparison']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get comparison list.
     */
    public function getComparison(Request $request)
    {
        $sessionId = $request->session()->getId();
        $comparison = $this->comparisonService->getComparison($sessionId);

        return response()->json([
            'products' => $comparison,
            'count' => $comparison->count(),
        ]);
    }

    /**
     * Compare products.
     */
    public function compare(Request $request)
    {
        $sessionId = $request->session()->getId();
        $comparison = $this->comparisonService->getComparison($sessionId);
        
        $productIds = $request->input('product_ids', $comparison->pluck('id')->toArray());
        
        if (empty($productIds)) {
            return redirect()->route('marketplace.index')
                ->withErrors(['error' => 'No products to compare']);
        }

        $request->validate([
            'product_ids' => 'nullable|array|max:4',
            'product_ids.*' => 'required|uuid|exists:products,id',
        ]);

        $comparisonData = $this->comparisonService->compareProducts($productIds);

        return Inertia::render('Marketplace/Product/Compare', [
            'comparison' => $comparisonData,
        ]);
    }

    /**
     * Get current effective price (API).
     */
    public function getEffectivePrice(Product $product, Request $request)
    {
        $pricingService = app(\App\Services\DynamicPricingService::class);
        $effectivePrice = $pricingService->calculateEffectivePrice($product);

        return response()->json([
            'product_id' => $product->id,
            'base_price' => (float) ($product->base_price ?? $product->price),
            'effective_price' => $effectivePrice,
            'pricing_rules_enabled' => $product->pricing_rules_enabled,
        ]);
    }
}
