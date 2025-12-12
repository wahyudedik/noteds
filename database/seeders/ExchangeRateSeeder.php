<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Seed supported exchange rates used by the currency service.
     */
    public function run(): void
    {
        $pairs = [
            [
                'from_currency' => 'USD',
                'to_currency' => 'IDR',
                'rate' => 16652.50,
                'notes' => 'Updated Dec 12, 2025 - Current market rate USD→IDR.',
            ],
            [
                'from_currency' => 'IDR',
                'to_currency' => 'USD',
                'rate' => round(1 / 16652.50, 8),
                'notes' => 'Updated Dec 12, 2025 - Inverse rate IDR→USD.',
            ],
            [
                'from_currency' => 'USD',
                'to_currency' => 'SAR',
                'rate' => 3.75,
                'notes' => 'Standard rate USD→SAR.',
            ],
            [
                'from_currency' => 'SAR',
                'to_currency' => 'USD',
                'rate' => round(1 / 3.75, 6),
                'notes' => 'Inverse rate SAR→USD.',
            ],
            [
                'from_currency' => 'IDR',
                'to_currency' => 'SAR',
                'rate' => 4437.60,
                'notes' => 'Updated Dec 12, 2025 - Current market rate IDR→SAR.',
            ],
            [
                'from_currency' => 'SAR',
                'to_currency' => 'IDR',
                'rate' => round(1 / 4437.60, 8),
                'notes' => 'Updated Dec 12, 2025 - Inverse rate SAR→IDR.',
            ],
            [
                'from_currency' => 'USD',
                'to_currency' => 'USD',
                'rate' => 1,
                'notes' => 'Identity rate to simplify conversions.',
            ],
            [
                'from_currency' => 'IDR',
                'to_currency' => 'IDR',
                'rate' => 1,
                'notes' => 'Identity rate to simplify conversions.',
            ],
            [
                'from_currency' => 'SAR',
                'to_currency' => 'SAR',
                'rate' => 1,
                'notes' => 'Identity rate to simplify conversions.',
            ],
        ];

        foreach ($pairs as $pair) {
            ExchangeRate::updateOrCreate(
                [
                    'from_currency' => $pair['from_currency'],
                    'to_currency' => $pair['to_currency'],
                ],
                [
                    'rate' => $pair['rate'],
                    'is_active' => true,
                    'notes' => $pair['notes'],
                ]
            );
        }
    }
}
