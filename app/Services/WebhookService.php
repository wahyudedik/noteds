<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Trigger webhooks for a specific event.
     */
    public function trigger(string $event, array $payload, ?\App\Models\User $user = null): void
    {
        $query = Webhook::where('event', $event)
            ->where('is_active', true);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $webhooks = $query->get();

        foreach ($webhooks as $webhook) {
            $this->sendWebhook($webhook, $payload);
        }
    }

    /**
     * Send webhook to a specific endpoint.
     */
    protected function sendWebhook(Webhook $webhook, array $payload): void
    {
        try {
            $signedPayload = $this->signPayload($payload, $webhook->secret);

            $response = Http::timeout(10)
                ->post($webhook->url, [
                    'event' => $webhook->event,
                    'payload' => $signedPayload,
                    'timestamp' => now()->toIso8601String(),
                ]);

            if ($response->successful()) {
                $webhook->incrementSuccess();
            } else {
                $webhook->incrementFailure();
                Log::warning('Webhook failed', [
                    'webhook_id' => $webhook->id,
                    'url' => $webhook->url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $webhook->incrementFailure();
            Log::error('Webhook exception', [
                'webhook_id' => $webhook->id,
                'url' => $webhook->url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sign payload with webhook secret.
     */
    protected function signPayload(array $payload, string $secret): array
    {
        $payloadString = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadString, $secret);

        return [
            'data' => $payload,
            'signature' => $signature,
        ];
    }

    /**
     * Verify webhook signature.
     */
    public function verifySignature(string $signature, array $payload, string $secret): bool
    {
        $payloadString = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $payloadString, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}

