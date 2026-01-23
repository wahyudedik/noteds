<?php

namespace App\Jobs;

use App\Models\TopUp;
use App\Services\MidtransService;
use App\Services\TopUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTopUpWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function __construct(private array $webhookData) {}

    public function handle(MidtransService $midtransService, TopUpService $topUpService): void
    {
        try {
            $orderId = $this->webhookData['order_id'] ?? null;
            if (!$orderId || !str_starts_with($orderId, 'TOPUP-')) {
                Log::warning('ProcessTopUpWebhook: invalid order_id', ['order_id' => $orderId]);
                return;
            }

            $topUpId = str_replace('TOPUP-', '', $orderId);
            $topUp = TopUp::find($topUpId);
            if (!$topUp) {
                Log::warning('ProcessTopUpWebhook: topup not found', ['order_id' => $orderId]);
                return;
            }

            if (!$midtransService->verifyWebhookSignature($this->webhookData)) {
                Log::warning('ProcessTopUpWebhook: invalid signature', ['order_id' => $orderId]);
                return;
            }

            if (isset($this->webhookData['transaction_id'])) {
                $topUp->update([
                    'midtrans_transaction_id' => $this->webhookData['transaction_id'],
                ]);
            }

            $transactionStatus = $this->webhookData['transaction_status'] ?? null;
            $fraudStatus = $this->webhookData['fraud_status'] ?? null;

            if ($transactionStatus === 'settlement') {
                $topUpService->processTopUpSuccess($topUp);
                Log::info("Top-up processed successfully (settlement): {$topUpId}");
            } elseif ($transactionStatus === 'capture') {
                if ($fraudStatus === 'deny') {
                    $topUp->markAsFailed();
                    Log::info("Top-up payment failed due to fraud: {$orderId}, status: {$transactionStatus}, fraud_status: {$fraudStatus}");
                } else {
                    Log::info("Top-up payment captured but not settled yet: {$orderId}");
                }
            } elseif ($transactionStatus === 'pending') {
                Log::info("Top-up payment pending: {$orderId}");
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $topUp->markAsFailed();
                Log::info("Top-up payment failed: {$orderId}, status: {$transactionStatus}");
            }
        } catch (\Exception $e) {
            Log::error('ProcessTopUpWebhook failed: ' . $e->getMessage(), [
                'webhook_data' => $this->webhookData,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('ProcessTopUpWebhook permanently failed', [
            'webhook_data' => $this->webhookData,
            'exception' => $e->getMessage(),
        ]);

        try {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new class($this->webhookData, $e) extends \Illuminate\Notifications\Notification {
                    use \Illuminate\Bus\Queueable;
                    public function __construct(private array $data, private \Throwable $e) {}
                    public function via($notifiable): array { return ['database']; }
                    public function toArray($notifiable): array {
                        return [
                            'type' => 'webhook_failed',
                            'title' => 'Webhook Top-Up Failed',
                            'message' => 'Top-Up webhook processing failed permanently. Check logs.',
                            'order_id' => $this->data['order_id'] ?? null,
                            'transaction_status' => $this->data['transaction_status'] ?? null,
                            'error' => $this->e->getMessage(),
                        ];
                    }
                });
            }
        } catch (\Exception $notifyEx) {
            Log::error('Failed to notify admins about top-up webhook failure: ' . $notifyEx->getMessage());
        }
    }
}
