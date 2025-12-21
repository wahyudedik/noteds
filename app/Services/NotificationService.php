<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notify seller about new order.
     */
    public function notifyNewOrder(Order $order): void
    {
        $seller = $order->product->seller;
        
        $seller->notify(new \App\Notifications\NewOrderNotification($order));
    }

    /**
     * Notify admin about withdrawal request.
     */
    public function notifyWithdrawalRequest(Withdrawal $withdrawal): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\WithdrawalRequestNotification($withdrawal));
        }
    }

    /**
     * Notify user about withdrawal status.
     */
    public function notifyWithdrawalStatus(Withdrawal $withdrawal): void
    {
        $withdrawal->user->notify(new \App\Notifications\WithdrawalStatusNotification($withdrawal));
    }
}

