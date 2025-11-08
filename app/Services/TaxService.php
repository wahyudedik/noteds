<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Setting;
use App\Models\TaxRule;
use App\Models\User;

class TaxService
{
    /**
     * Determine tax context for a purchase.
     */
    public function resolveTaxForPurchase(Note $note, ?User $buyer = null, ?string $noteCategory = null): array
    {
        $countryCode = $this->determineCountryCode($buyer);
        $rule = $this->findApplicableRule($countryCode, $noteCategory);

        $taxPercent = $rule?->tax_percent ?? Setting::getDefaultTaxPercent();
        $isInclusive = $rule?->is_inclusive ?? Setting::isTaxInclusiveDefault();
        $country = $rule?->country_code ?? $countryCode;

        return [
            'tax_percent' => (float) $taxPercent,
            'is_inclusive' => (bool) $isInclusive,
            'country_code' => $country,
        ];
    }

    /**
     * Calculate tax breakdown for a given base amount.
     */
    public function calculateAmounts(float $baseAmount, array $taxContext): array
    {
        $percent = $taxContext['tax_percent'] ?? 0.0;
        $inclusive = $taxContext['is_inclusive'] ?? true;

        if ($percent <= 0) {
            return [
                'tax_percent' => 0.0,
                'tax_amount' => 0.0,
                'price_excluding_tax' => $baseAmount,
                'total_amount' => $baseAmount,
                'tax_inclusive' => $inclusive,
            ];
        }

        if ($inclusive) {
            $priceExcludingTax = $baseAmount / (1 + ($percent / 100));
            $taxAmount = $baseAmount - $priceExcludingTax;
            $totalAmount = $baseAmount;
        } else {
            $priceExcludingTax = $baseAmount;
            $taxAmount = $baseAmount * ($percent / 100);
            $totalAmount = $baseAmount + $taxAmount;
        }

        return [
            'tax_percent' => round($percent, 2),
            'tax_amount' => round($taxAmount, 2),
            'price_excluding_tax' => round($priceExcludingTax, 2),
            'total_amount' => round($totalAmount, 2),
            'tax_inclusive' => $inclusive,
        ];
    }

    private function determineCountryCode(?User $buyer): ?string
    {
        if (!$buyer) {
            return 'ID';
        }

        if (!empty($buyer->locale) && str_contains($buyer->locale, '_')) {
            return strtoupper(substr($buyer->locale, strpos($buyer->locale, '_') + 1));
        }

        if (!empty($buyer->currency)) {
            return match (strtoupper($buyer->currency)) {
                'IDR' => 'ID',
                'USD' => 'US',
                'SGD' => 'SG',
                'MYR' => 'MY',
                'EUR' => 'EU',
                default => 'ID',
            };
        }

        return 'ID';
    }

    private function findApplicableRule(?string $countryCode, ?string $noteCategory): ?TaxRule
    {
        if (!$countryCode) {
            return null;
        }

        return TaxRule::query()
            ->where('country_code', strtoupper($countryCode))
            ->where('is_active', true)
            ->when($noteCategory, fn ($query) => $query->where('note_category', $noteCategory))
            ->orderByRaw('CASE WHEN note_category IS NULL THEN 1 ELSE 0 END')
            ->first();
    }
}

