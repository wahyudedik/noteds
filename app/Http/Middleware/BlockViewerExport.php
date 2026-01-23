<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockViewerExport
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->hasRole('viewer')) {
            abort(403, 'Unauthorized. Viewer cannot export data.');
        }
        return $next($request);
    }
}
