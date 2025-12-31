<?php

namespace App\Jobs;

use App\Services\TopUpService;
use App\Models\TopUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTopUpWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $webhookData
    ) {}

    public function handle(TopUpService $topUpService): void
    {
        try {
            $orderId = $this->webhookData['order_id'] ?? null;
            $transactionStatus = $this->webhookData['transaction_status'] ?? null;

            if (!$orderId || !str_starts_with($orderId, 'TOPUP-')) {
                return;
            }

            $topUpId = str_replace('TOPUP-', '', $orderId);
            $topUp = TopUp::find($topUpId);

            if (!$topUp) {
                Log::warning("TopUp not found: {$topUpId}");
                return;
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                if ($this->webhookData['fraud_status'] === 'accept') {
                    $topUp->update([
                        'midtrans_transaction_id' => $this->webhookData['transaction_id'] ?? null,
                    ]);
                    $topUpService->processTopUpSuccess($topUp);
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $topUp->markAsFailed();
            }
        } catch (\Exception $e) {
            Log::error('ProcessTopUpWebhook failed: ' . $e->getMessage(), [
                'webhook_data' => $this->webhookData,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
