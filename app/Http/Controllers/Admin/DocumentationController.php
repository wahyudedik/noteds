<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentationRequest;
use App\Http\Requests\UpdateDocumentationRequest;
use App\Models\Documentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $documentations = Documentation::with('creator')
            ->when(request('category'), function ($query) {
                return $query->where('category', request('category'));
            })
            ->when(request('search'), function ($query) {
                return $query->where('title', 'like', '%' . request('search') . '%')
                    ->orWhere('summary', 'like', '%' . request('search') . '%');
            })
            ->ordered()
            ->latest()
            ->paginate(20);

        $categories = [
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
        ];

        return view('admin.documentations.index', compact('documentations', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = [
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
        ];

        return view('admin.documentations.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->has('is_active');

        // Ensure links is array format
        if (isset($validated['links']) && !empty($validated['links'])) {
            $links = [];
            foreach ($validated['links'] as $link) {
                if (isset($link['title']) && isset($link['url'])) {
                    $links[] = [
                        'title' => $link['title'],
                        'url' => $link['url'],
                    ];
                }
            }
            $validated['links'] = $links;
        } else {
            $validated['links'] = [];
        }

        // Handle tags from input
        if ($request->has('tags_input') && !empty($request->tags_input)) {
            $tags = array_filter(array_map('trim', explode(',', $request->tags_input)));
            $validated['tags'] = $tags;
        } else {
            $validated['tags'] = [];
        }

        Documentation::create($validated);

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Documentation $documentation): View
    {
        $documentation->load('creator');
        
        // Increment view count (async in production)
        $documentation->incrementViewCount();

        return view('admin.documentations.show', compact('documentation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Documentation $documentation): View
    {
        $categories = [
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
        ];

        return view('admin.documentations.edit', compact('documentation', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentationRequest $request, Documentation $documentation): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        // Ensure links is array format
        if (isset($validated['links']) && !empty($validated['links'])) {
            $links = [];
            foreach ($validated['links'] as $link) {
                if (isset($link['title']) && isset($link['url'])) {
                    $links[] = [
                        'title' => $link['title'],
                        'url' => $link['url'],
                    ];
                }
            }
            $validated['links'] = $links;
        } else {
            $validated['links'] = [];
        }

        // Handle tags from input
        if ($request->has('tags_input') && !empty($request->tags_input)) {
            $tags = array_filter(array_map('trim', explode(',', $request->tags_input)));
            $validated['tags'] = $tags;
        } else {
            $validated['tags'] = [];
        }

        $documentation->update($validated);

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documentation $documentation): RedirectResponse
    {
        $documentation->delete();

        return redirect()->route('admin.documentations.index')
            ->with('success', 'Documentation deleted successfully.');
    }
}
