<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\StockSignalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSignalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $stockCode = null
    ) {
        $this->onQueue('signals');
    }

    /**
     * Execute the job.
     */
    public function handle(StockSignalService $signalService): void
    {
        try {
            if ($this->stockCode) {
                $stock = Stock::where('code', $this->stockCode)->first();
                if ($stock) {
                    $this->generateSignalsForStock($stock, $signalService);
                } else {
                    Log::warning('Stock not found for signal generation', ['code' => $this->stockCode]);
                }
            } else {
                // Generate signals for all active stocks
                $stocks = Stock::active()->get();
                
                foreach ($stocks as $stock) {
                    $this->generateSignalsForStock($stock, $signalService);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error generating stock signals', [
                'stock_code' => $this->stockCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate signals for a single stock.
     */
    protected function generateSignalsForStock(Stock $stock, StockSignalService $signalService): void
    {
        try {
            Log::info('Generating signals for stock', ['stock_code' => $stock->code]);
            
            $signals = $signalService->generateSignals($stock, 'ensemble');
            
            Log::info('Generated signals for stock', [
                'stock_code' => $stock->code,
                'signals_count' => $signals->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating signals for stock', [
                'stock_code' => $stock->code,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

