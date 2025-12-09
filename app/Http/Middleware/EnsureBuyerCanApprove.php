<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerCanApprove
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if (!$order instanceof ServiceOrder) {
            abort(404);
        }

        // Check if buyer can approve work
        if (!$order->canBuyerApprove(auth()->user())) {
            abort(403, 'Anda tidak dapat menyetujui pekerjaan untuk order ini.');
        }

        return $next($request);
    }
}
