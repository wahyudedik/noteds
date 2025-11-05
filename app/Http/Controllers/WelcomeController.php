<?php

namespace App\Http\Controllers;

use App\Models\LandingPageSection;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    /**
     * Display the welcome/landing page with dynamic sections.
     */
    public function index(): View
    {
        // Get all active sections ordered by order field
        $sections = LandingPageSection::active()
            ->ordered()
            ->get()
            ->filter(function ($section) {
                // Filter promo sections by date validity
                if ($section->section_type === 'promo') {
                    return $section->isValid();
                }
                return true;
            })
            ->values(); // Re-index array to ensure valid collection

        // Group sections by type for easier rendering
        $groupedSections = $sections->groupBy('section_type');

        // Get featured notes for landing page
        $featuredHero = \App\Models\FeaturedNote::active()
            ->byLocation('landing_hero')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->first();

        // Track impression for featured hero
        if ($featuredHero && auth()->check()) {
            $featuredHero->incrementImpressions();
        }

        $featuredCarousel = \App\Models\FeaturedNote::active()
            ->byLocation('landing_carousel')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        // Track impressions for featured carousel
        if ($featuredCarousel->count() > 0 && auth()->check()) {
            foreach ($featuredCarousel as $featured) {
                $featured->incrementImpressions();
            }
        }

        return view('welcome', compact('sections', 'groupedSections', 'featuredHero', 'featuredCarousel'));
    }
}
