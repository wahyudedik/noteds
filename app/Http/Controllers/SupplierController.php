<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Services\SupplierService;
use App\Services\SupplierRecommendationService;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierService $supplierService,
        private SupplierRecommendationService $recommendationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'category' => $request->get('category'),
            'location' => $request->get('location'),
            'min_rating' => $request->get('min_rating'),
            'verified_only' => $request->boolean('verified_only'),
            'active_only' => true,
            'sort_by' => $request->get('sort_by', 'rating'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];

        $suppliers = $this->supplierService->searchSuppliers($filters);

        // Format suppliers for frontend
        $formattedSuppliers = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->supplier_name,
                'description' => $supplier->description,
                'location' => $supplier->location,
                'rating' => $supplier->rating,
                'review_count' => $supplier->review_count,
                'specialties' => $supplier->specialties ?? [],
                'min_order' => $supplier->min_order_amount,
                'is_verified' => $supplier->is_verified,
                'seller_profile' => [
                    'id' => $supplier->seller->id,
                    'name' => $supplier->seller->name,
                    'business_name' => $supplier->seller->business_name,
                ],
            ];
        });

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $formattedSuppliers,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $businessTypes = $this->recommendationService->getBusinessTypes();

        return Inertia::render('Suppliers/Create', [
            'businessTypes' => $businessTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = $this->supplierService->registerSupplier(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier berhasil didaftarkan. Menunggu verifikasi admin.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Supplier $supplier): Response
    {
        $details = $this->supplierService->getSupplierDetails($supplier);

        return Inertia::render('Suppliers/Show', [
            'supplier' => $details['supplier'],
            'reviews' => $details['reviews'],
            'products' => $details['products'],
            'stats' => $details['stats'],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Supplier $supplier): Response
    {
        $this->authorize('update', $supplier);

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $this->supplierService->updateSupplier($supplier, $request->validated());

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier berhasil diupdate.');
    }

    /**
     * Get business types for API.
     */
    public function businessTypes(): \Illuminate\Http\JsonResponse
    {
        $businessTypes = $this->recommendationService->getBusinessTypes();

        return response()->json($businessTypes);
    }
}
