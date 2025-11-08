<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendMarketplaceDailyDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:daily-digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim ringkasan penjualan harian kepada setiap seller.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $since = now()->subDay();

        $transactions = Transaction::with(['seller', 'note'])
            ->where('status', 'success')
            ->whereNotNull('note_id')
            ->where('created_at', '>=', $since)
            ->get()
            ->groupBy('seller_id');

        foreach ($transactions as $sellerId => $sellerTransactions) {
            $seller = $sellerTransactions->first()?->seller;
            if (!$seller) {
                continue;
            }

            $totalSales = $sellerTransactions->count();
            $revenue = $sellerTransactions->sum('amount');

            $notes = $sellerTransactions->groupBy('note_id')
                ->map(function ($transactions) {
                    $note = $transactions->first()->note;
                    return [
                        'note_id' => $note?->id,
                        'title' => $note?->title,
                        'sales' => $transactions->count(),
                        'revenue' => $transactions->sum('amount'),
                    ];
                })
                ->sortByDesc('sales')
                ->values();

            $topNote = $notes->first();

            $summary = [
                'total_sales' => $totalSales,
                'revenue' => $revenue,
                'notes' => $notes->toArray(),
                'top_note_title' => $topNote['title'] ?? null,
                'top_note_sales' => $topNote['sales'] ?? 0,
            ];

            $notificationService->notifySellerDailyDigest($seller, $summary);
        }

        $this->info('Marketplace daily digests dispatched.');

        return Command::SUCCESS;
    }
}
