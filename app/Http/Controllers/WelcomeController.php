<?php

namespace App\Http\Controllers;

use App\Models\LandingPageSection;
use App\Models\CmsPage;
use App\Services\RecommendationService;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct(private RecommendationService $recommendationService)
    {
    }

    /**
     * Display the welcome/landing page with dynamic sections.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // For authenticated users, show personalized homepage
        if ($user) {
            return $this->personalizedHomepage($user);
        }
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

        // Extract CMS highlight section (only the first active one)
        $cmsHighlightSection = $sections->first(function ($section) {
            return $section->section_type === 'cms_pages';
        });

        // Remove CMS highlight section from general collection to avoid double rendering
        $sections = $sections->reject(function ($section) {
            return $section->section_type === 'cms_pages';
        });

        // Group sections by type for easier rendering
        $groupedSections = $sections->groupBy('section_type');

        // Get featured notes for landing page
        $featuredHero = \App\Models\FeaturedNote::active()
            ->byLocation('landing_hero')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->first();

        // Track impression for featured hero (track for all users, not just authenticated)
        if ($featuredHero) {
            $featuredHero->incrementImpressions();
        }

        $featuredCarousel = \App\Models\FeaturedNote::active()
            ->byLocation('landing_carousel')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        // Track impressions for featured carousel (track for all users, not just authenticated)
        if ($featuredCarousel->count() > 0) {
            foreach ($featuredCarousel as $featured) {
                $featured->incrementImpressions();
            }
        }

        $highlightedCmsPages = collect();

        if ($cmsHighlightSection) {
            $limit = (int) data_get($cmsHighlightSection->content, 'limit', 3);
            if ($limit <= 0) {
                $limit = 3;
            }

            $highlightedCmsPages = CmsPage::active()
                ->latest()
                ->limit($limit)
                ->get();
        }

        return view('welcome', compact(
            'sections',
            'groupedSections',
            'featuredHero',
            'featuredCarousel',
            'cmsHighlightSection',
            'highlightedCmsPages'
        ));
    }

    /**
     * Display personalized homepage for authenticated users.
     */
    protected function personalizedHomepage($user): View
    {
        // Update preferences from browsing history (async, don't block)
        try {
            $this->recommendationService->updatePreferencesFromBrowsingHistory($user);
        } catch (\Exception $e) {
            // Silently fail, don't break the page
        }

        // Get personalized recommendations
        $recommendations = $this->recommendationService->getRecommendations($user, 12);
        
        // Get recently viewed notes
        $recentlyViewed = $this->recommendationService->getRecentlyViewed($user, 12);
        
        // Get user preferences
        $preferences = $user->preference;
        
        // Get featured notes (still show these)
        $featuredHero = \App\Models\FeaturedNote::active()
            ->byLocation('landing_hero')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->first();

        if ($featuredHero) {
            $featuredHero->incrementImpressions();
        }

        $featuredCarousel = \App\Models\FeaturedNote::active()
            ->byLocation('landing_carousel')
            ->with(['note.tags', 'note.user', 'note.reviews'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($featuredCarousel->count() > 0) {
            foreach ($featuredCarousel as $featured) {
                $featured->incrementImpressions();
            }
        }

        return view('home.personalized', compact(
            'recommendations',
            'recentlyViewed',
            'preferences',
            'featuredHero',
            'featuredCarousel'
        ));
    }
}
