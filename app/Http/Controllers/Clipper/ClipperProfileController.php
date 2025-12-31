<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\ClipperOnboardingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClipperProfileController extends Controller
{
    public function __construct(
        private ClipperOnboardingService $onboardingService
    ) {}

    public function create()
    {
        // Check if user already has clipper role
        if (auth()->user()->isClipper()) {
            return redirect()->route('clipper.profile.show')
                ->with('info', 'You are already registered as a clipper.');
        }

        return Inertia::render('Clipper/Profile/Create', [
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'portfolio_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'skills' => 'nullable|array',
            'goals' => 'nullable|array',
        ]);

        try {
            $this->onboardingService->registerClipper(auth()->user(), $validated);

            return redirect()->route('clipper.profile.show')
                ->with('success', 'Clipper profile created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show()
    {
        $user = auth()->user();
        $stats = $this->onboardingService->getClipperStats($user);

        return Inertia::render('Clipper/Profile/Show', [
            'user' => $user,
            'stats' => $stats,
            'isClipper' => $user->isClipper(),
        ]);
    }

    public function edit()
    {
        $user = auth()->user();

        if (!$user->isClipper()) {
            return redirect()->route('clipper.profile.create');
        }

        return Inertia::render('Clipper/Profile/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'portfolio_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'skills' => 'nullable|array',
            'goals' => 'nullable|array',
        ]);

        try {
            $this->onboardingService->updateProfile(auth()->user(), $validated);

            return redirect()->route('clipper.profile.show')
                ->with('success', 'Clipper profile updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

