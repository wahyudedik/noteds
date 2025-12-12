<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'exchange-rates:update';

    /**
     * The console command description.
     */
    protected $description = 'Update exchange rates to latest market values';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║         Exchange Rates Update - December 12, 2025            ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

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
                'from_currency' => 'SAR',
                'to_currency' => 'IDR',
                'new_rate' => 4437.60,
                'notes' => 'Updated Dec 12, 2025 - Current market rate SAR→IDR (1 SAR = 4,437.60 IDR).',
            ],
            [
                'from_currency' => 'IDR',
                'to_currency' => 'SAR',
                'new_rate' => round(1 / 4437.60, 8),
                'notes' => 'Updated Dec 12, 2025 - Inverse rate IDR→SAR.',
            ],
        ];

        $this->info('Updating exchange rates...');
        $this->newLine();

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
                    $this->line("✅ {$from}→{$to}: {$oldRate} → {$update['new_rate']}");
                } else {
                    $this->line("✨ {$from}→{$to}: Created with rate {$update['new_rate']}");
                }
            }

            DB::commit();

            $this->newLine();
            $this->info('╔══════════════════════════════════════════════════════════════╗');
            $this->info('║              ✅ All rates updated successfully!              ║');
            $this->info('╚══════════════════════════════════════════════════════════════╝');
            $this->newLine();

            $this->info('New rates:');
            $this->line('  • USD → IDR: 16,652.50');
            $this->line('  • IDR → USD: ' . round(1 / 16652.50, 8));
            $this->line('  • IDR → SAR: 4,437.60');
            $this->line('  • SAR → IDR: ' . round(1 / 4437.60, 8));
            $this->newLine();

            $this->info('Next steps:');
            $this->line('  1. Clear cache: php artisan cache:clear');
            $this->line('  2. Visit admin panel: http://noteds.test/admin/exchange-rates');
            $this->line('  3. Verify rates are updated');
            $this->line('  4. Test in marketplace to see new prices');
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error updating rates: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
