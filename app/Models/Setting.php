<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get setting value with type casting.
     */
    public function getValueAttribute($value)
    {
        if ($this->type === 'boolean') {
            return $value === '1' || $value === true || $value === 'true';
        }
        
        if ($this->type === 'json') {
            return json_decode($value, true);
        }
        
        if ($this->type === 'number') {
            return is_numeric($value) ? (float) $value : 0;
        }
        
        return $value;
    }

    /**
     * Set setting value with type conversion.
     */
    public function setValueAttribute($value)
    {
        $type = $this->attributes['type'] ?? $this->type;

        if ($type === 'boolean') {
            $this->attributes['value'] = $value ? '1' : '0';
        } elseif ($type === 'json') {
            $this->attributes['value'] = json_encode($value);
        } elseif ($type === 'number') {
            $this->attributes['value'] = is_numeric($value) ? (string) $value : '0';
        } else {
            $this->attributes['value'] = is_array($value) ? json_encode($value) : (string) $value;
        }
    }

    /**
     * Get premium price (monthly) in Rupiah.
     * 
     * @return float
     */
    public static function getPremiumPrice(): float
    {
        $price = static::where('key', 'premium_price_monthly')
            ->where('group', 'subscription')
            ->first();

        return $price && is_numeric($price->value) ? (float) $price->value : 25000.0;
    }

    /**
     * Format premium price for display.
     * 
     * @param bool $short Whether to return short format (e.g., "Rp25k/mo")
     * @return string
     */
    public static function formatPremiumPrice(bool $short = false): string
    {
        $price = self::getPremiumPrice();

        if ($short && $price >= 1000) {
            // Format as "Rp25k/mo" for short display
            $kPrice = $price / 1000;
            return 'Rp' . number_format($kPrice, 0, ',', '.') . 'k/mo';
        }

        // Full format: "Rp 25.000/bulan"
        return 'Rp ' . number_format($price, 0, ',', '.') . '/bulan';
    }

    /**
     * Get setting value by key and group.
     * 
     * @param string $key
     * @param string $group
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting(string $key, string $group = 'general', $default = null)
    {
        $setting = static::where('key', $key)
            ->where('group', $group)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key and group.
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string|null $description
     * @return static
     */
    public static function setSetting(string $key, $value, string $type = 'string', string $group = 'general', ?string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key, 'group' => $group],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get referral signup reward amount (in Rupiah).
     * 
     * @return float
     */
    public static function getReferralSignupReward(): float
    {
        $reward = static::where('key', 'referral_reward_signup')
            ->where('group', 'referral')
            ->first();

        return $reward && is_numeric($reward->value) ? (float) $reward->value : 5000.0;
    }

    /**
     * Get referral transaction commission percentage.
     * 
     * @return float
     */
    public static function getReferralCommissionPercent(): float
    {
        $percent = static::where('key', 'referral_reward_commission_percent')
            ->where('group', 'referral')
            ->first();

        return $percent && is_numeric($percent->value) ? (float) $percent->value : 5.0;
    }

    /**
     * Get platform commission percentage (for every transaction).
     * 
     * @return float
     */
    public static function getPlatformCommissionPercent(): float
    {
        $percent = static::where('key', 'platform_commission_percent')
            ->where('group', 'marketplace')
            ->first();

        return $percent && is_numeric($percent->value) ? (float) $percent->value : 20.0;
    }

    /**
     * Get creator commission percentage (only for original creator on resale).
     * 
     * @return float
     */
    public static function getCreatorCommissionPercent(): float
    {
        $percent = static::where('key', 'creator_commission_percent')
            ->where('group', 'marketplace')
            ->first();

        return $percent && is_numeric($percent->value) ? (float) $percent->value : 0.0;
    }

    /**
     * Get premium buyer discount percentage.
     * Premium buyers get exclusive discount on all purchases.
     * 
     * @return float
     */
    public static function getPremiumBuyerDiscountPercent(): float
    {
        $percent = static::where('key', 'premium_buyer_discount_percent')
            ->where('group', 'marketplace')
            ->first();

        return $percent && is_numeric($percent->value) ? (float) $percent->value : 10.0; // Default 10%
    }

    /**
     * Get default tax percent.
     */
    public static function getDefaultTaxPercent(): float
    {
        $percent = static::where('key', 'tax_default_percent')
            ->where('group', 'marketplace')
            ->first();

        return $percent && is_numeric($percent->value) ? (float) $percent->value : 0.0;
    }

    /**
     * Determine if tax is inclusive by default.
     */
    public static function isTaxInclusiveDefault(): bool
    {
        $inclusive = static::where('key', 'tax_inclusive_default')
            ->where('group', 'marketplace')
            ->first();

        return $inclusive ? (bool) $inclusive->value : true;
    }

    /**
     * Get default minimum price for paid notes.
     */
    public static function getDefaultMinPrice(): float
    {
        $setting = static::where('key', 'min_price_default')
            ->where('group', 'marketplace')
            ->first();

        return $setting && is_numeric($setting->value) ? (float) $setting->value : 0.0;
    }

    /**
     * Get recommended price multiplier.
     */
    public static function getRecommendedPriceMultiplier(): float
    {
        $setting = static::where('key', 'recommended_price_multiplier')
            ->where('group', 'marketplace')
            ->first();

        return $setting && is_numeric($setting->value) ? (float) $setting->value : 1.0;
    }

    /**
     * Get list of category-specific minimum prices.
     */
    public static function getCategoryMinPriceList(): array
    {
        $setting = static::where('key', 'min_price_categories')
            ->where('group', 'marketplace')
            ->first();

        $rules = is_array($setting?->value) ? $setting->value : [];

        return array_map(function ($rule) {
            return [
                'tag_slug' => $rule['tag_slug'] ?? null,
                'tag_name' => $rule['tag_name'] ?? null,
                'min_price' => isset($rule['min_price']) ? (float) $rule['min_price'] : 0.0,
            ];
        }, $rules);
    }

    /**
     * Get associative array of category minimum prices indexed by slug.
     */
    public static function getCategoryMinPrices(): array
    {
        $rules = self::getCategoryMinPriceList();

        $mapped = [];
        foreach ($rules as $rule) {
            if (!empty($rule['tag_slug'])) {
                $mapped[$rule['tag_slug']] = $rule['min_price'];
            }
        }

        return $mapped;
    }

    /**
     * Persist category-specific minimum prices.
     */
    public static function setCategoryMinPriceList(array $rules): void
    {
        static::setSetting(
            'min_price_categories',
            $rules,
            'json',
            'marketplace',
            'Category-specific minimum prices for notes'
        );
    }

    /**
     * Get featured notes location labels.
     */
    public static function getFeaturedLocationLabels(): array
    {
        return [
            'landing_hero' => 'Landing Hero',
            'landing_carousel' => 'Landing Carousel',
            'marketplace_banner' => 'Marketplace Banner',
            'marketplace_grid' => 'Marketplace Grid',
            'popup_welcome' => 'Popup Welcome',
            'popup_exit' => 'Popup Exit Intent',
            'popup_interstitial' => 'Popup Interstitial',
        ];
    }

    /**
     * Get available durations (in days) for featured notes.
     */
    public static function getFeaturedDurations(): array
    {
        return [7, 14, 30];
    }

    /**
     * Default pricing for featured notes per location/duration.
     */
    public static function getDefaultFeaturedPricing(): array
    {
        return [
            'landing_hero' => [7 => 150000, 14 => 280000, 30 => 500000],
            'landing_carousel' => [7 => 100000, 14 => 180000, 30 => 350000],
            'marketplace_banner' => [7 => 75000, 14 => 140000, 30 => 250000],
            'marketplace_grid' => [7 => 50000, 14 => 90000, 30 => 150000],
            'popup_welcome' => [7 => 100000, 14 => 180000, 30 => 350000],
            'popup_exit' => [7 => 80000, 14 => 150000, 30 => 280000],
            'popup_interstitial' => [7 => 60000, 14 => 110000, 30 => 200000],
        ];
    }

    /**
     * Retrieve featured notes pricing configuration.
     */
    public static function getFeaturedPricing(): array
    {
        $locations = array_keys(self::getFeaturedLocationLabels());
        $durations = self::getFeaturedDurations();
        $defaults = self::getDefaultFeaturedPricing();
        $pricing = [];

        foreach ($locations as $location) {
            foreach ($durations as $duration) {
                $key = "featured_price_{$location}_{$duration}";
                $default = $defaults[$location][$duration] ?? 50000;
                $price = self::getSetting($key, 'featured_notes', $default);
                $pricing[$location][$duration] = (float) $price;
            }
        }

        return $pricing;
    }
}
