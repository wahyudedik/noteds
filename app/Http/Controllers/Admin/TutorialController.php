<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TutorialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $tutorials = Tutorial::with('author')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            })
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->featured !== null, function ($query) use ($request) {
                return $query->where('featured', $request->featured);
            })
            ->orderBy('order')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tutorials.index', compact('tutorials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = [
            'design' => 'Desain grafis, UI/UX',
            'web' => 'Web dev & backend',
            'photo' => 'Fotografi & video editing',
            'business' => 'Productivity & creative business',
        ];

        return view('admin.tutorials.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tutorials,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:design,web,photo,business'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:published,draft'],
            'featured' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Tutorial::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $slug;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            if (!Storage::disk('public')->exists('tutorials/thumbnails')) {
                Storage::disk('public')->makeDirectory('tutorials/thumbnails');
            }
            
            $thumbnailPath = $request->file('thumbnail')->store('tutorials/thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $validated['author_id'] = auth()->id();
        $validated['featured'] = $request->has('featured');
        $validated['order'] = $validated['order'] ?? 0;

        Tutorial::create($validated);

        return redirect()->route('admin.tutorials.index')
            ->with('success', 'Tutorial berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     * Redirect to public show page.
     */
    public function show(Tutorial $tutorial): RedirectResponse
    {
        return redirect()->route('tuts.show', $tutorial->slug);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tutorial $tutorial): View
    {
        $categories = [
            'design' => 'Desain grafis, UI/UX',
            'web' => 'Web dev & backend',
            'photo' => 'Fotografi & video editing',
            'business' => 'Productivity & creative business',
        ];

        return view('admin.tutorials.edit', compact('tutorial', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tutorial $tutorial): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tutorials,slug,' . $tutorial->id],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:design,web,photo,business'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:published,draft'],
            'featured' => ['boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Generate slug if not provided or changed
        if (empty($validated['slug']) || $validated['slug'] !== $tutorial->slug) {
            if (empty($validated['slug'])) {
                $baseSlug = Str::slug($validated['title']);
            } else {
                $baseSlug = Str::slug($validated['slug']);
            }
            
            $slug = $baseSlug;
            $counter = 1;
            
            while (Tutorial::where('slug', $slug)->where('id', '!=', $tutorial->id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $slug;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($tutorial->thumbnail && Storage::disk('public')->exists($tutorial->thumbnail)) {
                Storage::disk('public')->delete($tutorial->thumbnail);
            }
            
            if (!Storage::disk('public')->exists('tutorials/thumbnails')) {
                Storage::disk('public')->makeDirectory('tutorials/thumbnails');
            }
            
            $thumbnailPath = $request->file('thumbnail')->store('tutorials/thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $validated['featured'] = $request->has('featured');
        $validated['order'] = $validated['order'] ?? $tutorial->order;

        $tutorial->update($validated);

        return redirect()->route('admin.tutorials.index')
            ->with('success', 'Tutorial berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tutorial $tutorial): RedirectResponse
    {
        // Delete thumbnail if exists
        if ($tutorial->thumbnail && Storage::disk('public')->exists($tutorial->thumbnail)) {
            Storage::disk('public')->delete($tutorial->thumbnail);
        }

        $tutorial->delete();

        return redirect()->route('admin.tutorials.index')
            ->with('success', 'Tutorial berhasil dihapus.');
    }
}
