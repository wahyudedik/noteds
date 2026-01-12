<?php

namespace App\Events;

use App\Models\Product;
use App\Models\ProductPricingRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PricingRuleApplied
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ProductPricingRule $rule,
        public Product $product,
        public float $originalPrice,
        public float $adjustedPrice
    ) {
        //
    }
}
