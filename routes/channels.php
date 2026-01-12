<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    return $order && $order->user_id === $user->id;
});

// Stock price updates (public channel)
Broadcast::channel('stock.{stockCode}.prices', function ($user, $stockCode) {
    return true; // Public channel
});

// Stock signal updates (public channel)
Broadcast::channel('stock.{stockCode}.signals', function ($user, $stockCode) {
    return true; // Public channel
});

// User watchlist updates (private channel)
Broadcast::channel('user.{userId}.watchlist', function ($user, $userId) {
    return $user->id === $userId;
});

