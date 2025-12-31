<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class RetryService
{
    /**
     * Retry configuration.
     */
    private const DEFAULT_CONFIG = [
        'max_retries' => 3,
        'initial_delay' => 60, // seconds
        'max_delay' => 3600, // 1 hour
        'multiplier' => 2, // double delay each retry
    ];

    /**
     * Execute a callable with retry logic and exponential backoff.
     */
    public function retry(callable $callback, array $config = [], ?string $context = null): mixed
    {
        $config = array_merge(self::DEFAULT_CONFIG, $config);
        $maxRetries = $config['max_retries'];
        $initialDelay = $config['initial_delay'];
        $maxDelay = $config['max_delay'];
        $multiplier = $config['multiplier'];

        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt >= $maxRetries) {
                    // All retries exhausted
                    Log::error('Retry exhausted', [
                        'context' => $context,
                        'attempts' => $attempt,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }

                // Calculate delay with exponential backoff
                $delay = min($initialDelay * pow($multiplier, $attempt - 1), $maxDelay);

                Log::warning('Retry attempt failed', [
                    'context' => $context,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'next_retry_in_seconds' => $delay,
                    'error' => $e->getMessage(),
                ]);

                // Sleep before retry
                sleep($delay);
            }
        }

        // Should not reach here, but just in case
        throw $lastException ?? new Exception('Retry failed without exception');
    }

    /**
     * Queue a retry job for later execution.
     */
    public function queueRetry(callable $callback, int $delaySeconds, array $config = [], ?string $context = null): void
    {
        // This would dispatch a job to the queue
        // For now, we'll just log it
        Log::info('Queue retry scheduled', [
            'context' => $context,
            'delay_seconds' => $delaySeconds,
        ]);

        // In production, you would dispatch a job:
        // dispatch(new RetryJob($callback, $config, $context))->delay(now()->addSeconds($delaySeconds));
    }
}

