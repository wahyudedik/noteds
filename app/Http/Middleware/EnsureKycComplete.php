<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycComplete
{
    /**
     * Handle an incoming request.
     * 
     * Redirects to profile edit page if user hasn't uploaded KTP and selfie.
     * Marketplace routes are excluded from this check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Allow marketplace routes (browsing only)
        if ($request->routeIs('marketplace.*') || $request->is('marketplace*')) {
            return $next($request);
        }
        
        // Allow dashboard and profile routes (user needs to access these to upload KTP/selfie)
        if ($request->routeIs('dashboard') || 
            $request->routeIs('profile.*') ||
            $request->routeIs('setup-username.*')) {
            return $next($request);
        }
        
        // Allow public routes
        if ($request->routeIs('welcome') || 
            $request->routeIs('cms.*') || 
            $request->routeIs('faq') ||
            $request->routeIs('contact.*') ||
            $request->routeIs('ecosystem.*') ||
            $request->routeIs('tuts.*') ||
            $request->routeIs('studio.index') ||
            $request->routeIs('simulators.*') ||
            $request->routeIs('public.profile.*')) {
            return $next($request);
        }
        
        // Check if user has uploaded document identity and selfie (admin tidak perlu verifikasi)
        if (!$user->hasRole('admin') && (!$user->ktp_path || !$user->selfie_path)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda dengan mengupload dokumen identitas (KTP atau Kartu Pelajar) dan foto selfie untuk mengakses fitur ini.');
        }
        
        return $next($request);
    }
}

