<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Documentation::query();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        $documentations = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(20);

        // Get unique categories for filter
        $categories = Documentation::distinct()->whereNotNull('category')->pluck('category');

        return Inertia::render('Admin/Documentations/Index', [
            'documentations' => $documentations,
            'filters' => [
                'status' => $request->status ?? 'all',
                'category' => $request->category ?? '',
                'search' => $request->search ?? '',
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Documentations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentations,slug',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        // Auto generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Documentation::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        Documentation::create($validated);

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Documentation $documentation)
    {
        return Inertia::render('Admin/Documentations/Edit', [
            'documentation' => $documentation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Documentation $documentation)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentations,slug,' . $documentation->id,
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        // Auto generate slug if not provided or if title changed
        if (empty($validated['slug']) || ($validated['title'] !== $documentation->title && !$request->has('slug'))) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Documentation::where('slug', $validated['slug'])
                ->where('id', '!=', $documentation->id)
                ->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        $documentation->update($validated);

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documentation $documentation)
    {
        $documentation->delete();

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation deleted successfully.');
    }

    /**
     * Toggle status between draft and published.
     */
    public function toggleStatus(Documentation $documentation)
    {
        $documentation->status = $documentation->status === 'draft' ? 'published' : 'draft';
        $documentation->save();

        return back()->with('success', 'Documentation status updated successfully.');
    }

    /**
     * Reorder documentations.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:documentations,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            Documentation::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return back()->with('success', 'Documentations reordered successfully.');
    }
}
