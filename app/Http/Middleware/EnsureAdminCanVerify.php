<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCanVerify
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403, 'Hanya admin yang dapat memverifikasi order.');
        }

        $order = $request->route('order');

        if (!$order instanceof ServiceOrder) {
            abort(404);
        }

        // Check if admin can verify
        if (!$order->canAdminVerify(auth()->user())) {
            abort(403, 'Order tidak memenuhi syarat untuk verifikasi admin.');
        }

        return $next($request);
    }
}
