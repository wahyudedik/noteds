<?php

namespace App\Http\Controllers;

use App\Services\SupplierRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierRecommendationController extends Controller
{
    public function __construct(
        private SupplierRecommendationService $recommendationService
    ) {}

    /**
     * Get supplier recommendations for business type.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'business_type' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $recommendations = $this->recommendationService->getRecommendedSuppliers(
            $request->input('business_type'),
            $request->input('location'),
            $request->input('limit', 5)
        );

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }
}
