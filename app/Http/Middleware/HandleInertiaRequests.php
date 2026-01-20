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
        $cartCount = 0;
        
        if ($user) {
            $userArray = $user->toArray();
            $userArray['avatar_url'] = $user->avatar_url;
            
            // Get cart count
            $cartCount = \App\Models\CartItem::where('user_id', $user->id)->count();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userArray,
            ],
            'settings' => $user ? $user->settings : null,
            'midtrans_client_key' => config('midtrans.client_key'),
            'midtrans_is_production' => config('midtrans.is_production'),
            'notifications' => $user 
                ? $user->unreadNotifications()->latest()->limit(10)->get()
                : [],
            'cart_count' => $cartCount,
        ];
    }
}
