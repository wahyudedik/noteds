<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display a listing of all documentation.
     */
    public function index(): View
    {
        $documentations = Documentation::active()
            ->with('creator')
            ->when(request('category'), function ($query) {
                return $query->where('category', request('category'));
            })
            ->when(request('search'), function ($query) {
                return $query->where('title', 'like', '%' . request('search') . '%')
                    ->orWhere('summary', 'like', '%' . request('search') . '%')
                    ->orWhereJsonContains('tags', request('search'));
            })
            ->ordered()
            ->latest()
            ->paginate(12);

        $categories = [
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
        ];

        // Get counts per category
        $categoryCounts = [];
        foreach ($categories as $key => $label) {
            $categoryCounts[$key] = Documentation::active()->where('category', $key)->count();
        }

        return view('40-shared/docs/index', compact('documentations', 'categories', 'categoryCounts'));
    }

    /**
     * Display documentation by category.
     */
    public function category(string $category): View
    {
        $validCategories = ['wiki', 'screenshot_guide', 'link_reference', 'troubleshooting', 'api_documentation', 'video_tutorial'];
        
        if (!in_array($category, $validCategories)) {
            abort(404);
        }

        $documentations = Documentation::active()
            ->with('creator')
            ->where('category', $category)
            ->when(request('search'), function ($query) {
                return $query->where('title', 'like', '%' . request('search') . '%')
                    ->orWhere('summary', 'like', '%' . request('search') . '%');
            })
            ->ordered()
            ->latest()
            ->paginate(12);

        $categories = [
            'wiki' => 'Wiki',
            'screenshot_guide' => 'Screenshot Guide',
            'link_reference' => 'Link Reference',
            'troubleshooting' => 'Troubleshooting',
            'api_documentation' => 'API Documentation',
            'video_tutorial' => 'Video Tutorial',
        ];

        return view('40-shared/docs/category', compact('documentations', 'categories', 'category'));
    }

    /**
     * Display a single documentation.
     */
    public function show(string $category, Documentation $documentation): View
    {
        // Verify category matches
        if ($documentation->category !== $category) {
            abort(404);
        }

        if (!$documentation->is_active && (!auth()->check() || !auth()->user()->hasRole('admin'))) {
            abort(404);
        }

        $documentation->load('creator');
        
        // Increment view count (only for non-admin)
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            $documentation->incrementViewCount();
        }

        // Get related documentations
        $relatedDocs = Documentation::active()
            ->where('category', $documentation->category)
            ->where('id', '!=', $documentation->id)
            ->ordered()
            ->limit(5)
            ->get();

        return view('40-shared/docs/show', compact('documentation', 'relatedDocs'));
    }

    /**
     * Mark documentation as helpful.
     */
    public function markHelpful(string $category, Documentation $documentation): RedirectResponse
    {
        // Verify category matches
        if ($documentation->category !== $category) {
            abort(404);
        }

        $documentation->incrementHelpfulCount();

        return back()->with('success', 'Thank you for your feedback!');
    }
}
