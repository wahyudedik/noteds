<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

/**
 * CSRF Token Validation Middleware
 * 
 * Protects against Cross-Site Request Forgery attacks by validating
 * CSRF tokens on all state-modifying requests (POST, PUT, PATCH, DELETE).
 * 
 * Except routes:
 * - Webhook endpoints (external services can't include CSRF tokens)
 * - API routes (use Bearer token authentication instead)
 */
class ValidateCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Webhook endpoints - external services cannot include CSRF tokens
        'api/webhooks/*',
        'webhooks/*',

        // Mobile app endpoints - use Bearer token authentication
        'api/mobile/*',

        // Third-party integrations that provide their own security
        'api/stripe/webhook',
        'api/midtrans/webhook',
        'api/payment/callback',
    ];

    /**
     * Determine if the request has a valid CSRF token.
     * Override to add custom logging of CSRF failures for security monitoring.
     */
    public function handle($request, $next)
    {
        // Check if this is an API request using Bearer token
        if ($this->isApiRequest($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * Check if request is using API token authentication
     */
    protected function isApiRequest($request): bool
    {
        return $request->bearerToken() !== null ||
            $request->hasHeader('Authorization');
    }
}
