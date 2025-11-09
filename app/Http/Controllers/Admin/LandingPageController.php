<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLandingPageSectionRequest;
use App\Http\Requests\UpdateLandingPageSectionRequest;
use App\Models\LandingPageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $sections = LandingPageSection::with('creator')
            ->when(request('type'), function ($query) {
                return $query->where('section_type', request('type'));
            })
            ->ordered()
            ->latest()
            ->paginate(20);

        $sectionTypes = [
            'hero' => 'Hero Section',
            'features' => 'Features Grid',
            'how_it_works' => 'How It Works',
            'premium_benefits' => 'Premium Benefits',
            'trust_indicators' => 'Trust Indicators',
            'testimonials' => 'Testimonials',
            'promo' => 'Promotional Section',
            'cms_pages' => 'CMS Highlight',
            'custom' => 'Custom Section',
        ];

        return view('admin.landing-page.index', compact('sections', 'sectionTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $sectionTypes = [
            'hero' => 'Hero Section',
            'features' => 'Features Grid',
            'how_it_works' => 'How It Works',
            'premium_benefits' => 'Premium Benefits',
            'trust_indicators' => 'Trust Indicators',
            'testimonials' => 'Testimonials',
            'promo' => 'Promotional Section',
            'cms_pages' => 'CMS Highlight',
            'custom' => 'Custom Section',
        ];

        return view('admin.landing-page.create', compact('sectionTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLandingPageSectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->has('is_active');

        LandingPageSection::create($validated);

        return redirect()->route('admin.landing-page.index')
            ->with('success', 'Landing page section created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LandingPageSection $landingPage): View
    {
        $landingPage->load('creator');
        return view('admin.landing-page.show', compact('landingPage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandingPageSection $landingPage): View
    {
        $sectionTypes = [
            'hero' => 'Hero Section',
            'features' => 'Features Grid',
            'how_it_works' => 'How It Works',
            'premium_benefits' => 'Premium Benefits',
            'trust_indicators' => 'Trust Indicators',
            'testimonials' => 'Testimonials',
            'promo' => 'Promotional Section',
            'cms_pages' => 'CMS Highlight',
            'custom' => 'Custom Section',
        ];

        return view('admin.landing-page.edit', compact('landingPage', 'sectionTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLandingPageSectionRequest $request, LandingPageSection $landingPage): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        $landingPage->update($validated);

        return redirect()->route('admin.landing-page.index')
            ->with('success', 'Landing page section updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandingPageSection $landingPage): RedirectResponse
    {
        $landingPage->delete();

        return redirect()->route('admin.landing-page.index')
            ->with('success', 'Landing page section deleted successfully.');
    }
}
