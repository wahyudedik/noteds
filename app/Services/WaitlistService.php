<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductWaitlist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProductAvailableNotification;

class WaitlistService
{
    /**
     * Add user to waitlist.
     */
    public function addToWaitlist(Product $product, User $user): ProductWaitlist
    {
        if (!$product->is_waitlist_enabled) {
            throw new \Exception('Waitlist is not enabled for this product');
        }

        return ProductWaitlist::firstOrCreate([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Remove user from waitlist.
     */
    public function removeFromWaitlist(Product $product, User $user): bool
    {
        return ProductWaitlist::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * Check stock and notify waitlist.
     */
    public function checkAndNotify(Product $product): void
    {
        if (!$product->is_waitlist_enabled) {
            return;
        }

        $notifyAtStock = $product->waitlist_notify_at_stock ?? 0;
        $currentStock = $product->stock ?? 0;

        if ($currentStock >= $notifyAtStock) {
            $this->notifyWaitlist($product);
        }
    }

    /**
     * Notify waitlist users.
     */
    public function notifyWaitlist(Product $product): void
    {
        $waitlistEntries = $product->waitlists()
            ->unnotified()
            ->with('user')
            ->get();

        foreach ($waitlistEntries as $entry) {
            if ($entry->user) {
                // Send notification (handles both email and in-app via notification system)
                $entry->user->notify(new ProductAvailableNotification($product));
            }

            // Mark as notified
            $entry->notify();
        }
    }
}

