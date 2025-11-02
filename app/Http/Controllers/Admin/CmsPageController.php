<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pages = CmsPage::latest()->get();
        return view('admin.cms-pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.cms-pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:cms_pages,slug'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CmsPage::create($validated);

        return redirect()->route('admin.cms-pages.index')
            ->with('success', 'CMS page created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CmsPage $cmsPage): View
    {
        return view('admin.cms-pages.edit', compact('cmsPage'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CmsPage $cmsPage): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:cms_pages,slug,' . $cmsPage->id],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cmsPage->update($validated);

        return redirect()->route('admin.cms-pages.index')
            ->with('success', 'CMS page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CmsPage $cmsPage): RedirectResponse
    {
        $cmsPage->delete();

        return redirect()->route('admin.cms-pages.index')
            ->with('success', 'CMS page deleted successfully.');
    }
}
