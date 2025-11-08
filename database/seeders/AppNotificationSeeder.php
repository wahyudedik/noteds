<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AppNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(12)->get();

        foreach ($users as $user) {
            foreach ($this->notifications() as $payload) {
                AppNotification::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => $payload['type'],
                        'title' => $payload['title'],
                    ],
                    [
                        'message' => $payload['message'],
                        'link' => $payload['link'],
                        'is_read' => Arr::random([true, false, false]),
                        'data' => $payload['data'],
                    ]
                );
            }
        }
    }

    protected function notifications(): array
    {
        return [
            [
                'type' => 'purchase',
                'title' => '🎉 Pembelian Berhasil',
                'message' => 'Catatan terbaru kamu sudah ada di perpustakaan! Yuk cek sekarang.',
                'link' => '/dashboard',
                'data' => ['cta' => 'Lihat catatan'],
            ],
            [
                'type' => 'review',
                'title' => '⭐ Ada Review Baru',
                'message' => 'Seorang pembeli memberikan review untuk catatan yang kamu jual.',
                'link' => '/seller/reviews',
                'data' => ['rating' => rand(4, 5)],
            ],
            [
                'type' => 'withdraw',
                'title' => '💰 Status Withdraw',
                'message' => 'Permintaan penarikan dana kamu sudah diproses oleh admin.',
                'link' => '/wallet',
                'data' => ['status' => Arr::random(['approved', 'rejected'])],
            ],
            [
                'type' => 'support_reply',
                'title' => '🛠️ Balasan Tiket Support',
                'message' => 'Tim Noteds sudah membalas tiket bantuan kamu.',
                'link' => '/support/tickets',
                'data' => ['priority' => Arr::random(['low', 'medium', 'high'])],
            ],
        ];
    }
}


