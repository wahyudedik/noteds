<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminCleanupTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:cleanup-transactions {--status=pending : Filter by status (pending|failed|success)} {--days=1 : Age in days} {--force : Skip confirmation} {--export : Export to CSV before deletion} {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advanced admin transaction cleanup tool with filtering and export options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        $days = (int) $this->option('days');
        $force = $this->option('force');
        $export = $this->option('export');
        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $this->line('');
        $this->info('🔧 ADMIN TRANSACTION CLEANUP TOOL');
        $this->line('═══════════════════════════════════════════════');
        $this->line('');

        // Build query
        $query = Transaction::where('created_at', '<', $cutoffDate);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'asc')->get();

        if ($transactions->isEmpty()) {
            $this->info("✅ No {$status} transactions older than {$days} day(s).");
            return;
        }

        // Display filters
        $this->line('Filters Applied:');
        $this->line("  • Status: {$status}");
        $this->line("  • Older than: {$days} day(s) ({$cutoffDate->format('Y-m-d H:i:s')})");
        $this->line("  • Dry Run: " . ($dryRun ? 'YES (No changes)' : 'NO (Will modify DB)'));
        $this->line('');

        $this->warn("Found {$transactions->count()} transaction(s) matching criteria");
        $this->line('');

        // Display details
        $this->displayTransactions($transactions);

        // Export if requested
        if ($export && !$dryRun) {
            $this->exportToCSV($transactions);
        }

        // Dry run - stop here
        if ($dryRun) {
            $this->info('✅ Dry run completed - no changes made to database');
            return;
        }

        // Ask for confirmation
        if (!$force && !$this->confirm('Proceed with deletion?', false)) {
            $this->line('Cancelled - no changes made.');
            return;
        }

        // Delete
        $deleted = 0;
        $failed = 0;

        DB::beginTransaction();

        try {
            foreach ($transactions as $transaction) {
                try {
                    Log::warning('Admin cleanup transaction', [
                        'id' => $transaction->id,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'created_at' => $transaction->created_at,
                        'deleted_by_command' => 'admin:cleanup-transactions',
                        'timestamp' => now(),
                    ]);

                    $transaction->delete();
                    $deleted++;
                } catch (\Exception $e) {
                    Log::error('Failed to delete transaction', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failed++;
                }
            }

            DB::commit();

            $this->line('');
            $this->info("✅ Cleanup completed!");
            $this->table(
                ['Result', 'Count'],
                [
                    ['Deleted', $deleted],
                    ['Failed', $failed],
                    ['Total Processed', $transactions->count()],
                ]
            );

            if ($failed > 0) {
                $this->warn("⚠️  {$failed} transaction(s) failed to delete - check logs");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Transaction failed - all changes rolled back');
            $this->error('Error: ' . $e->getMessage());
            Log::error('Admin cleanup transaction failed', [
                'error' => $e->getMessage(),
                'status' => $status,
                'days' => $days,
            ]);
        }
    }

    private function displayTransactions($transactions): void
    {
        $rows = [];
        $totalAmount = 0;

        foreach ($transactions as $t) {
            $hours = $t->created_at->diffInHours(now());
            $days = floor($hours / 24);

            $rows[] = [
                substr($t->id, 0, 8) . '...',
                $t->status,
                number_format($t->amount, 2),
                $t->currency,
                "{$days}d ago",
                $t->buyer->name ?? 'N/A',
            ];

            $totalAmount += $t->amount;
        }

        $this->table(
            ['ID', 'Status', 'Amount', 'Curr', 'Age', 'Buyer'],
            $rows
        );

        $this->line('');
        $this->line("Total Amount: " . number_format($totalAmount, 2));
    }

    private function exportToCSV($transactions): void
    {
        $filename = storage_path('logs/cleanup_export_' . now()->format('Y-m-d_H-i-s') . '.csv');

        $fp = fopen($filename, 'w');

        // Headers
        fputcsv($fp, ['ID', 'Status', 'Amount', 'Currency', 'Payment Method', 'Buyer', 'Created At']);

        // Data
        foreach ($transactions as $t) {
            fputcsv($fp, [
                $t->id,
                $t->status,
                $t->amount,
                $t->currency,
                $t->payment_method,
                $t->buyer->name,
                $t->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($fp);

        $this->info("✅ Exported to: {$filename}");
    }
}
