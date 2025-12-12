#!/usr/bin/env php
<?php

/**
 * Exchange Rates Update Script
 * Purpose: Update exchange rates in database to latest values
 * Run: php update_exchange_rates.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         Exchange Rates Update Script - Dec 12, 2025          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$updates = [
    [
        'from_currency' => 'USD',
        'to_currency' => 'IDR',
        'new_rate' => 16652.50,
        'notes' => 'Updated Dec 12, 2025 - Current market rate USD→IDR.',
    ],
    [
        'from_currency' => 'IDR',
        'to_currency' => 'USD',
        'new_rate' => round(1 / 16652.50, 8),
        'notes' => 'Updated Dec 12, 2025 - Inverse rate IDR→USD.',
    ],
    [
        'from_currency' => 'IDR',
        'to_currency' => 'SAR',
        'new_rate' => 4437.60,
        'notes' => 'Updated Dec 12, 2025 - Current market rate IDR→SAR.',
    ],
    [
        'from_currency' => 'SAR',
        'to_currency' => 'IDR',
        'new_rate' => round(1 / 4437.60, 8),
        'notes' => 'Updated Dec 12, 2025 - Inverse rate SAR→IDR.',
    ],
];

echo "Updating exchange rates...\n\n";

try {
    DB::beginTransaction();

    foreach ($updates as $update) {
        $from = $update['from_currency'];
        $to = $update['to_currency'];
        $oldRate = ExchangeRate::where('from_currency', $from)
            ->where('to_currency', $to)
            ->value('rate');

        $result = ExchangeRate::updateOrCreate(
            [
                'from_currency' => $from,
                'to_currency' => $to,
            ],
            [
                'rate' => $update['new_rate'],
                'is_active' => true,
                'notes' => $update['notes'],
            ]
        );

        if ($oldRate) {
            echo "✅ {$from}→{$to}: {$oldRate} → {$update['new_rate']}\n";
        } else {
            echo "✨ {$from}→{$to}: Created with rate {$update['new_rate']}\n";
        }
    }

    DB::commit();

    echo "\n╔══════════════════════════════════════════════════════════════╗\n";
    echo "║              ✅ All rates updated successfully!              ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";

    echo "New rates:\n";
    echo "  • USD → IDR: 16,652.50\n";
    echo "  • IDR → USD: " . round(1 / 16652.50, 8) . "\n";
    echo "  • IDR → SAR: 4,437.60\n";
    echo "  • SAR → IDR: " . round(1 / 4437.60, 8) . "\n\n";

    echo "Next steps:\n";
    echo "  1. Clear cache: php artisan cache:clear\n";
    echo "  2. Visit admin panel: http://noteds.test/admin/exchange-rates\n";
    echo "  3. Verify rates are updated\n";
    echo "  4. Test in marketplace to see new prices\n\n";

    exit(0);
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error updating rates: {$e->getMessage()}\n\n";
    exit(1);
}
