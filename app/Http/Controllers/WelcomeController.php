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
            });

        // Group sections by type for easier rendering
        $groupedSections = $sections->groupBy('section_type');

        return view('welcome', compact('sections', 'groupedSections'));
    }
}
