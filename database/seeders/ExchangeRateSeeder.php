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
                'rate' => 15500.0000,
                'notes' => 'Seeded USD→IDR rate for testing multi-currency flows.',
            ],
            [
                'from_currency' => 'IDR',
                'to_currency' => 'USD',
                'rate' => round(1 / 15500, 6),
                'notes' => 'Seeded IDR→USD rate derived from base USD rate.',
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


