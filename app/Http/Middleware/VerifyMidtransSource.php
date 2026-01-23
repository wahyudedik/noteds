<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyMidtransSource
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $allowedIps = (array) config('midtrans.allowed_ips', []);
        $callbackToken = config('midtrans.callback_token');
        $headerToken = $request->header('X-Callback-Token');

        $ipAllowed = empty($allowedIps) || in_array($ip, $allowedIps, true);
        $tokenValid = empty($callbackToken) || ($headerToken === $callbackToken);

        if (!$ipAllowed || !$tokenValid) {
            Log::warning('Midtrans webhook source verification failed', [
                'ip' => $ip,
                'ip_allowed' => $ipAllowed,
                'has_callback_token' => !empty($callbackToken),
                'header_token_matches' => $tokenValid,
                'path' => $request->path(),
            ]);
            // Do NOT block; controllers will still return 200 to prevent retries.
        }

        return $next($request);
    }
}
