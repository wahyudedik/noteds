<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(
        private FileStorageService $fileStorageService
    ) {}

    public function index(Request $request)
    {
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

        $products = $query->latest()->paginate(12);

        return Inertia::render('Marketplace/Index', [
            'products' => $products,
        ]);
    }

    public function show(Product $product)
    {
        $product->increment('views_count');
        $product->load('seller');

        return Inertia::render('Marketplace/Product/Show', [
            'product' => $product,
        ]);
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
            $validated['image'] = $request->file('image')->store('products/images', 'public');
        }

        $product = Product::create($validated);

        if ($request->hasFile('file_download')) {
            $filePath = $this->fileStorageService->uploadProductFile(
                $request->file('file_download'),
                $product
            );
            $product->update(['file_download' => $filePath]);
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
            $validated['image'] = $request->file('image')->store('products/images', 'public');
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

        return redirect()->route('marketplace.index')
            ->with('success', 'Product deleted successfully.');
    }
}
