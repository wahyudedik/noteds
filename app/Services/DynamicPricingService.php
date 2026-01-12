<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPricingRule;
use App\Models\ProductPricingRuleApplication;
use App\Models\Order;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DynamicPricingService
{
    /**
     * Calculate effective price with all applicable rules.
     */
    public function calculateEffectivePrice(Product $product): float
    {
        if (!$product->pricing_rules_enabled) {
            return (float) ($product->base_price ?? $product->price);
        }

        $basePrice = $product->base_price ?? $product->price;
        $applicableRules = $this->getApplicableRules($product);

        if ($applicableRules->isEmpty()) {
            return (float) $basePrice;
        }

        // Use highest priority rule
        $rule = $applicableRules->first();
        $adjustedPrice = $rule->calculatePrice($basePrice);

        return $adjustedPrice;
    }

    /**
     * Apply active rules and return new price.
     */
    public function applyPricingRules(Product $product): ?float
    {
        if (!$product->pricing_rules_enabled) {
            return null;
        }

        $effectivePrice = $this->calculateEffectivePrice($product);
        $basePrice = $product->base_price ?? $product->price;

        if ($effectivePrice !== $basePrice) {
            $product->update(['current_dynamic_price' => $effectivePrice]);

            // Record application if there's an applicable rule
            $applicableRules = $this->getApplicableRules($product);
            if ($applicableRules->isNotEmpty()) {
                $rule = $applicableRules->first();
                $this->recordRuleApplication($rule, $product, $basePrice, $effectivePrice);
            }

            return $effectivePrice;
        }

        return null;
    }

    /**
     * Get all currently applicable rules.
     */
    public function getApplicableRules(Product $product): Collection
    {
        return ProductPricingRule::forProduct($product->id)
            ->active()
            ->byPriority()
            ->get()
            ->filter(function ($rule) use ($product) {
                return $rule->isApplicable();
            });
    }

    /**
     * Evaluate time-based rule.
     */
    public function evaluateTimeBasedRule(ProductPricingRule $rule): bool
    {
        // This is handled by the rule's isApplicable method
        return $rule->rule_type === 'time_based' && $rule->isApplicable();
    }

    /**
     * Evaluate stock-based rule.
     */
    public function evaluateStockBasedRule(ProductPricingRule $rule, Product $product): bool
    {
        // This is handled by the rule's isApplicable method
        return $rule->rule_type === 'stock_based' && $rule->isApplicable();
    }

    /**
     * Evaluate demand-based rule.
     */
    public function evaluateDemandBasedRule(ProductPricingRule $rule, Product $product): bool
    {
        // This is handled by the rule's isApplicable method
        return $rule->rule_type === 'demand_based' && $rule->isApplicable();
    }

    /**
     * Create new pricing rule.
     */
    public function createRule(array $data, Product $product): ProductPricingRule
    {
        $data['product_id'] = $product->id;
        $rule = ProductPricingRule::create($data);

        // Apply rules to update product price
        if ($product->pricing_rules_enabled) {
            $this->applyPricingRules($product);
        }

        return $rule;
    }

    /**
     * Update existing rule.
     */
    public function updateRule(ProductPricingRule $rule, array $data): ProductPricingRule
    {
        $rule->update($data);

        // Reapply rules to update product price
        if ($rule->product->pricing_rules_enabled) {
            $this->applyPricingRules($rule->product);
        }

        return $rule->fresh();
    }

    /**
     * Deactivate rule.
     */
    public function deactivateRule(ProductPricingRule $rule): void
    {
        $rule->update(['is_active' => false]);

        // Reapply rules to update product price
        if ($rule->product->pricing_rules_enabled) {
            $this->applyPricingRules($rule->product);
        }
    }

    /**
     * Record rule application.
     */
    public function recordRuleApplication(
        ProductPricingRule $rule,
        Product $product,
        float $originalPrice,
        float $adjustedPrice,
        ?Order $order = null
    ): ProductPricingRuleApplication {
        return ProductPricingRuleApplication::create([
            'rule_id' => $rule->id,
            'product_id' => $product->id,
            'order_id' => $order?->id,
            'original_price' => $originalPrice,
            'adjusted_price' => $adjustedPrice,
            'adjustment_amount' => $adjustedPrice - $originalPrice,
            'applied_at' => now(),
        ]);
    }

    /**
     * Process scheduled pricing (cron job).
     */
    public function processScheduledPricing(): void
    {
        // Process time-based and demand-based rules
        $products = Product::withDynamicPricing()
            ->with('activePricingRules')
            ->get();

        foreach ($products as $product) {
            $this->applyPricingRules($product);
        }
    }
}

