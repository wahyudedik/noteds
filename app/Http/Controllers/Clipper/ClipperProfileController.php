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

            return redirect()->route('dashboard')
                ->with('success', 'Clipper registration submitted. Please wait for admin approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show()
    {
        $user = auth()->user();
        
        // Ensure we have a valid user
        if (!$user) {
            abort(404, 'User not found');
        }
        
        $stats = $this->onboardingService->getClipperStats($user);

        // Convert user to array and add avatar_url
        $profileUserArray = $user->toArray();
        $profileUserArray['avatar_url'] = $user->avatar_url;

        // Get clipper profile if exists
        $clipperProfile = \App\Models\ClipperProfile::where('user_id', $user->id)
            ->where('status', 'verified')
            ->first();

        return Inertia::render('Clipper/Profile/Show', [
            'profileUser' => $profileUserArray,
            'isOwnProfile' => true,
            'clipperProfile' => $clipperProfile,
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

