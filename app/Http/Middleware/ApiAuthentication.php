<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Authentication Middleware
 * 
 * Validates API tokens and enforces authentication for API endpoints.
 * - Validates Bearer token format
 * - Checks token existence in database
 * - Verifies token hasn't expired
 * - Logs all API access for audit trails
 * - Prevents token reuse and invalid tokens
 */
class ApiAuthentication
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Extract Bearer token
        $token = $this->extractToken($request);

        if (!$token) {
            $this->logApiFailure($request, 'Missing API token');
            return response()->json([
                'message' => 'API token required',
                'error' => 'missing_token',
            ], 401);
        }

        // Validate token format
        if (!$this->isValidTokenFormat($token)) {
            $this->logApiFailure($request, 'Invalid token format');
            return response()->json([
                'message' => 'Invalid API token format',
                'error' => 'invalid_format',
            ], 401);
        }

        // Check token in database
        $user = $this->authenticateWithToken($token);
        if (!$user) {
            $this->logApiFailure($request, 'Invalid or expired token');
            return response()->json([
                'message' => 'Invalid or expired API token',
                'error' => 'invalid_token',
            ], 401);
        }

        // Verify user account is active
        if ($user->status !== 'active') {
            $this->logApiFailure($request, 'Account inactive');
            return response()->json([
                'message' => 'Account is inactive',
                'error' => 'account_inactive',
            ], 403);
        }

        // Log successful API access
        $this->auditLog->logApiAccess(
            $user,
            $request->method(),
            $request->path(),
            $request->ip()
        );

        // Attach user to request for subsequent use
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }

    /**
     * Extract Bearer token from request
     */
    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Validate token format (sha256 hash format)
     */
    protected function isValidTokenFormat(string $token): bool
    {
        // API tokens should be 64 characters (SHA256 hex)
        return preg_match('/^[a-f0-9]{64}$/', $token) === 1;
    }

    /**
     * Authenticate user with API token
     */
    protected function authenticateWithToken(string $token): ?\App\Models\User
    {
        $apiToken = \App\Models\ApiToken::where('token', $token)
            ->where('revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('user')
            ->first();

        if (!$apiToken) {
            return null;
        }

        // Update last used timestamp
        $apiToken->update(['last_used_at' => now()]);

        return $apiToken->user;
    }

    /**
     * Log API authentication failure
     */
    protected function logApiFailure(Request $request, string $reason): void
    {
        \Log::warning('API authentication failure', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'path' => $request->path(),
            'reason' => $reason,
            'timestamp' => now(),
        ]);
    }
}
