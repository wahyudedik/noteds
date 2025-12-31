<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Services\BrandOnboardingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandRegistrationController extends Controller
{
    public function __construct(
        private BrandOnboardingService $onboardingService
    ) {}

    public function create()
    {
        // Check if user already has brand role
        if (auth()->user()->isBrand()) {
            return redirect()->route('clipper.campaigns.index')
                ->with('info', 'You are already registered as a brand.');
        }

        return Inertia::render('Clipper/BrandRegistration/Create', [
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_field' => 'required|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        try {
            $this->onboardingService->registerBrand(auth()->user(), $validated);

            return redirect()->route('dashboard')
                ->with('success', 'Brand registration submitted. Please wait for admin approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show()
    {
        $user = auth()->user();

        return Inertia::render('Clipper/BrandRegistration/Show', [
            'user' => $user,
            'isBrand' => $user->isBrand(),
        ]);
    }

    public function edit()
    {
        $user = auth()->user();

        if (!$user->isBrand()) {
            return redirect()->route('clipper.brand-registration.create');
        }

        return Inertia::render('Clipper/BrandRegistration/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_field' => 'required|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        try {
            auth()->user()->update($validated);

            return redirect()->route('clipper.brand-registration.show')
                ->with('success', 'Brand information updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

