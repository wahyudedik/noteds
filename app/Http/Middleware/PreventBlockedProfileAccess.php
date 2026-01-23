<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PreventBlockedProfileAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $viewer = $request->user();
        if (!$viewer) {
            return $next($request);
        }
        $targetUserId = $request->route('user');
        if (!$targetUserId) {
            return $next($request);
        }
        $blocked = DB::table('user_blocks')
            ->where('blocker_id', $targetUserId)
            ->where('blocked_id', $viewer->id)
            ->exists();
        if ($blocked) {
            return redirect()->route('home')->withErrors(['error' => 'Anda diblokir oleh pengguna ini.']);
        }
        return $next($request);
    }
}
