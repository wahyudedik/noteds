<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceUser
{
    /**
     * Handle an incoming request.
     * Workspace users (user_workspaces role) can only access workspace-related routes.
     * They cannot access dashboard, marketplace, notes, wallet, etc.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();
        
        // Admin can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Workspace users can only access workspace routes
        if ($user->role === 'user_workspaces') {
            // Block access to marketplace, wallet, dashboard, featured notes, referral, subscription
            $blockedRoutes = [
                'marketplace.*',
                'wallet.*',
                'dashboard',
                'featured-notes.*',
                'referral.*',
                'subscription.*',
                'simulators.*',
            ];
            
            foreach ($blockedRoutes as $pattern) {
                if ($request->routeIs($pattern)) {
                    return redirect()->route('workspaces.index')
                        ->with('error', 'Sebagai Workspace User, Anda hanya dapat mengakses workspace. Untuk akses fitur lainnya, silakan daftar sebagai Seller atau Buyer.');
                }
            }
            
            // Allow workspace-related routes
            $allowedRoutes = [
                'workspaces.*',
                'mynoteds.*',
                'ai-memory.*',
                'folders.*',
                'notes.*', // Workspace users can create notes in workspace
                'logout',
                'locale.*',
                'setup-username.*',
            ];
            
            // Check if route is allowed
            $isAllowed = false;
            foreach ($allowedRoutes as $pattern) {
                if ($request->routeIs($pattern)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if ($isAllowed) {
                return $next($request);
            }
            
            // Redirect to workspace index if trying to access other routes
            return redirect()->route('workspaces.index')
                ->with('error', 'Sebagai Workspace User, Anda hanya dapat mengakses workspace. Untuk akses fitur lainnya, silakan daftar sebagai Seller atau Buyer.');
        }

        // Other roles (buyer, seller) cannot access workspace-only routes
        // This will be handled by other middleware
        return $next($request);
    }
}
