<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $userArray = null;
        $shouldLoadNotifications = false;
        
        if ($user) {
            $userArray = $user->toArray();
            $userArray['avatar_url'] = $user->avatar_url;
            
            $shouldLoadNotifications = $request->routeIs('home') 
                || $request->routeIs('dashboard') 
                || $request->routeIs('notifications.*');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userArray,
            ],
            'settings' => $user ? $user->settings : null,
            'notifications' => $shouldLoadNotifications && $user
                ? $user->unreadNotifications()->latest()->limit(10)->get()
                : [],
            'blocked_user_ids' => $user 
                ? \Illuminate\Support\Facades\DB::table('user_blocks')->where('blocker_id', $user->id)->pluck('blocked_id')
                : [],
        ];
    }
}
