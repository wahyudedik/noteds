<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users with active premium subscriptions
        $premiumUsers = User::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                  ->where('plan', 'premium')
                  ->where(function ($q) {
                      $q->whereNull('expired_at')
                        ->orWhere('expired_at', '>', now());
                  });
        })->take(3)->get();

        // If no premium users, use regular users (for testing)
        if ($premiumUsers->isEmpty()) {
            $premiumUsers = User::whereIn('role', ['seller', 'user_workspaces'])->take(3)->get();
        }

        if ($premiumUsers->isEmpty()) {
            return;
        }

        $events = [
            'note.purchased',
            'note.created',
            'note.updated',
            'transaction.completed',
            'withdraw.approved',
            'subscription.renewed',
        ];

        foreach ($premiumUsers as $user) {
            // Create 2-3 webhooks per user
            $webhookCount = rand(2, 3);

            for ($i = 0; $i < $webhookCount; $i++) {
                Webhook::create([
                    'user_id' => $user->id,
                    'name' => 'Webhook ' . ($i + 1) . ' - ' . $events[array_rand($events)],
                    'event' => $events[array_rand($events)],
                    'url' => 'https://example.com/webhook/' . Str::random(10),
                    'secret' => Str::random(32),
                    'is_active' => rand(0, 1) === 1,
                ]);
            }
        }
    }
}

