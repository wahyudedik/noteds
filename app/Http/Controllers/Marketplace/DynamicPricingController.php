<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPricingRule;
use App\Services\DynamicPricingService;
use App\Http\Requests\CreatePricingRuleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DynamicPricingController extends Controller
{
    public function __construct(
        private DynamicPricingService $pricingService
    ) {}

    /**
     * List pricing rules for seller's products.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user();
        $filter = $request->get('filter', 'all'); // all, active, inactive
        $ruleType = $request->get('rule_type');

        $query = ProductPricingRule::whereHas('product', function ($q) use ($seller) {
            $q->where('user_id', $seller->id);
        })->with(['product']);

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($ruleType) {
            $query->where('rule_type', $ruleType);
        }

        $rules = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Marketplace/Seller/PricingRules/Index', [
            'rules' => $rules,
            'filter' => $filter,
            'rule_type' => $ruleType,
        ]);
    }

    /**
     * Show create rule form.
     */
    public function create(Request $request): Response
    {
        $seller = $request->user();
        $productId = $request->get('product_id');

        $products = Product::where('user_id', $seller->id)
            ->where('is_active', true)
            ->get();

        $selectedProduct = $productId ? Product::find($productId) : null;

        return Inertia::render('Marketplace/Seller/PricingRules/Create', [
            'products' => $products,
            'selected_product' => $selectedProduct,
        ]);
    }

    /**
     * Create new pricing rule.
     */
    public function store(CreatePricingRuleRequest $request)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $rule = $this->pricingService->createRule($validated, $product);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pricing rule created successfully',
                'rule' => $rule->load(['product']),
            ], 201);
        }

        return redirect()
            ->route('marketplace.seller.pricing-rules.show', $rule)
            ->with('success', 'Pricing rule created successfully');
    }

    /**
     * Show rule details.
     */
    public function show(ProductPricingRule $rule, Request $request): Response
    {
        // Authorize: seller must own the product
        if ($rule->product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $rule->load(['product', 'applications.order']);

        return Inertia::render('Marketplace/Seller/PricingRules/Show', [
            'rule' => $rule,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(ProductPricingRule $rule, Request $request): Response
    {
        // Authorize: seller must own the product
        if ($rule->product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $rule->load(['product']);

        return Inertia::render('Marketplace/Seller/PricingRules/Edit', [
            'rule' => $rule,
        ]);
    }

    /**
     * Update rule.
     */
    public function update(CreatePricingRuleRequest $request, ProductPricingRule $rule)
    {
        // Authorize: seller must own the product
        if ($rule->product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();
        $rule = $this->pricingService->updateRule($rule, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pricing rule updated successfully',
                'rule' => $rule->load(['product']),
            ]);
        }

        return redirect()
            ->route('marketplace.seller.pricing-rules.show', $rule)
            ->with('success', 'Pricing rule updated successfully');
    }

    /**
     * Delete rule.
     */
    public function destroy(ProductPricingRule $rule, Request $request)
    {
        // Authorize: seller must own the product
        if ($rule->product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $rule->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pricing rule deleted successfully',
            ]);
        }

        return redirect()
            ->route('marketplace.seller.pricing-rules.index')
            ->with('success', 'Pricing rule deleted successfully');
    }

    /**
     * Toggle rule active status.
     */
    public function toggle(ProductPricingRule $rule, Request $request)
    {
        // Authorize: seller must own the product
        if ($rule->product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $rule->update(['is_active' => !$rule->is_active]);

        // Reapply rules if activating
        if ($rule->is_active && $rule->product->pricing_rules_enabled) {
            $this->pricingService->applyPricingRules($rule->product);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Rule status updated successfully',
                'rule' => $rule->fresh(),
            ]);
        }

        return back()->with('success', 'Rule status updated successfully');
    }

    /**
     * Preview effective price with rules (API).
     */
    public function previewPrice(Request $request, Product $product)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $effectivePrice = $this->pricingService->calculateEffectivePrice($product);
        $applicableRules = $this->pricingService->getApplicableRules($product);

        return response()->json([
            'original_price' => (float) ($product->base_price ?? $product->price),
            'effective_price' => $effectivePrice,
            'applicable_rules' => $applicableRules->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'rule_type' => $r->rule_type,
                'priority' => $r->priority,
            ]),
        ]);
    }

    /**
     * Get currently applicable rules (API).
     */
    public function getApplicableRules(Product $product, Request $request)
    {
        // Authorize: seller must own the product
        if ($product->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $applicableRules = $this->pricingService->getApplicableRules($product);

        return response()->json($applicableRules);
    }
}
