<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:cleanup-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate cleanup summary report and send to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Generating Cleanup Summary Report...');
        $this->line('');

        // Get statistics for last 24 hours
        $yesterday = now()->subDay();
        
        $stats = [
            'pending_total' => Transaction::where('status', 'pending')->count(),
            'pending_old' => Transaction::where('status', 'pending')
                ->where('created_at', '<', $yesterday)
                ->count(),
            'success_24h' => Transaction::where('status', 'success')
                ->where('created_at', '>=', $yesterday)
                ->count(),
            'failed_24h' => Transaction::where('status', 'failed')
                ->where('created_at', '>=', $yesterday)
                ->count(),
            'total_pending_amount' => Transaction::where('status', 'pending')
                ->sum('amount'),
            'total_old_amount' => Transaction::where('status', 'pending')
                ->where('created_at', '<', $yesterday)
                ->sum('amount'),
        ];

        // Display summary
        $this->table(
            ['Metric', 'Count/Amount'],
            [
                ['Total Pending Transactions', $stats['pending_total']],
                ['Pending > 24 hours', $stats['pending_old']],
                ['Successful (Last 24h)', $stats['success_24h']],
                ['Failed (Last 24h)', $stats['failed_24h']],
                ['Total Pending Amount', 'Rp ' . number_format($stats['total_pending_amount'], 0)],
                ['Old Pending Amount', 'Rp ' . number_format($stats['total_old_amount'], 0)],
            ]
        );

        $this->line('');

        // Log the summary
        Log::info('Cleanup Summary Report', [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'stats' => $stats,
            'recommendations' => $this->getRecommendations($stats),
        ]);

        // Display recommendations
        if ($stats['pending_old'] > 0) {
            $this->warn('⚠️  ACTION REQUIRED:');
            $this->line('  • ' . $stats['pending_old'] . ' transactions are pending for >24 hours');
            $this->line('  • Total amount: Rp ' . number_format($stats['total_old_amount'], 0));
            $this->line('  • Run: php artisan transactions:cleanup-pending --days=1 --verify');
        } else {
            $this->info('✅ All systems clean! No old pending transactions.');
        }

        $this->info('');
        $this->info('📋 Report generated at: ' . now()->format('Y-m-d H:i:s'));
    }

    private function getRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['pending_old'] > 10) {
            $recommendations[] = 'High number of old pending transactions - Run cleanup immediately';
        }

        if ($stats['total_old_amount'] > 10000000) {
            $recommendations[] = 'Large amount in old pending transactions - Investigate manually';
        }

        if ($stats['failed_24h'] > $stats['success_24h']) {
            $recommendations[] = 'More failures than successes - Check payment gateway status';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'System operating normally';
        }

        return $recommendations;
    }
}
