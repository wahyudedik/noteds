<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReportPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:report-pending {--threshold=24 : Hours threshold to consider as suspicious}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report suspicious pending transactions that need attention';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $suspiciousDate = now()->subHours($threshold);

        // Get pending transactions
        $pendingTransactions = Transaction::where('status', 'pending')
            ->where('created_at', '<', $suspiciousDate)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info('✅ No suspicious pending transactions found.');
            return;
        }

        $this->warn("⚠️  Found {$pendingTransactions->count()} suspicious pending transactions (older than {$threshold} hours)");
        $this->line('');

        $totalAmount = 0;
        foreach ($pendingTransactions as $transaction) {
            $hours = $transaction->created_at->diffInHours(now());
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;

            $timeAgo = $days > 0 ? "{$days}d {$remainingHours}h" : "{$hours}h";

            $this->line("🔴 {$transaction->id}");
            $this->line("   Amount: " . number_format($transaction->amount, 2) . " {$transaction->currency}");
            $this->line("   Pending: {$timeAgo} ago");
            $this->line("   Midtrans: {$transaction->midtrans_order_id}");
            $this->line("   User: {$transaction->buyer->name} ({$transaction->buyer->email})");
            $this->line('');

            $totalAmount += $transaction->amount;
        }

        // Log summary
        Log::warning('Suspicious pending transactions report', [
            'count' => $pendingTransactions->count(),
            'total_amount' => $totalAmount,
            'threshold_hours' => $threshold,
            'transaction_ids' => $pendingTransactions->pluck('id')->toArray(),
        ]);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Transactions', $pendingTransactions->count()],
                ['Total Amount', number_format($totalAmount, 2)],
                ['Threshold', "{$threshold} hours"],
                ['Oldest Transaction', $pendingTransactions->first()->created_at->diffForHumans()],
            ]
        );

        $this->info('');
        $this->info('💡 Recommendation: Run "transactions:cleanup-pending --days=1 --verify" to clean up.');
    }
}
