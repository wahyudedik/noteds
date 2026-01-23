<?php

namespace App\Http\Controllers\Clipper;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRegistrationRequest;
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
        if (auth()->user()->isBrand() || auth()->user()->isAdmin()) {
            return redirect()->route('clipper.campaigns.index')
                ->with('info', 'You are already registered as a brand.');
        }

        return Inertia::render('Brand/Registration/Create', [
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreBrandRegistrationRequest $request)
    {
        $validated = $request->validated();

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

        return Inertia::render('Brand/Registration/Show', [
            'user' => $user,
            'isBrand' => $user->isBrand() || $user->isAdmin(),
        ]);
    }

    public function edit()
    {
        $user = auth()->user();

        if (!$user->isBrand() && !$user->isAdmin()) {
            return redirect()->route('clipper.brand-registration.create');
        }

        // Edit uses the same Create component but with existing data
        return Inertia::render('Brand/Registration/Create', [
            'user' => $user,
            'isEdit' => true,
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

