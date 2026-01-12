<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePortfolioRequest;
use App\Models\PortfolioRecommendation;
use App\Services\PortfolioRecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioRecommendationController extends Controller
{
    public function __construct(
        protected PortfolioRecommendationService $portfolioService
    ) {
        //
    }

    /**
     * Show portfolio generation form.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        // Check if user is premium
        if (!$this->isPremiumUser($user)) {
            return redirect()->route('portfolio.index')->withErrors([
                'message' => 'Portfolio recommendations are available for premium users only.',
            ]);
        }

        return Inertia::render('Portfolio/Generate');
    }

    /**
     * Generate portfolio recommendation (premium only).
     */
    public function generate(GeneratePortfolioRequest $request)
    {
        $user = $request->user();

        // Check if user is premium (implement your premium check logic)
        // For now, we'll assume there's a method to check premium status
        if (!$this->isPremiumUser($user)) {
            return redirect()->back()->withErrors([
                'message' => 'Portfolio recommendations are available for premium users only.',
            ]);
        }

        try {
            $recommendation = $this->portfolioService->generateRecommendation(
                $user,
                $request->risk_profile,
                $request->investment_amount,
                $request->investment_horizon
            );

            // Validate recommendation
            $errors = $this->portfolioService->validateRecommendation($recommendation);
            if (!empty($errors)) {
                $recommendation->delete();
                return redirect()->back()->withErrors(['validation' => $errors]);
            }

            return redirect()->route('portfolio.show', $recommendation->id)
                ->with('success', 'Portfolio recommendation generated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Failed to generate portfolio recommendation: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * List user's portfolio recommendations.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $recommendations = PortfolioRecommendation::where('user_id', $user->id)
            ->latest('generated_at')
            ->paginate(10);

        return Inertia::render('Portfolio/Index', [
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Show portfolio recommendation details.
     */
    public function show(PortfolioRecommendation $recommendation): Response
    {
        $user = auth()->user();

        // Ensure user owns this recommendation
        if ($recommendation->user_id !== $user->id) {
            abort(403);
        }

        $breakdown = $recommendation->getAllocationBreakdown();
        $riskMetrics = $recommendation->getRiskMetrics();

        return Inertia::render('Portfolio/Show', [
            'recommendation' => $recommendation,
            'breakdown' => $breakdown,
            'riskMetrics' => $riskMetrics,
        ]);
    }

    /**
     * Check if user is premium (implement your logic).
     */
    protected function isPremiumUser($user): bool
    {
        // Implement your premium user check logic
        // This could check a subscription, role, or feature flag
        // For now, return true for testing
        return true; // TODO: Implement actual premium check
    }
}

