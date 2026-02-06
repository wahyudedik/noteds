<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Notifications\DatabaseNotification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?: User::create([
            'name' => 'Demo User',
            'email' => 'demo@noteds.test',
            'password' => bcrypt('password'),
        ]);

        $now = now();

        $payloads = [
            ['type' => 'support_ticket_response', 'ticket_id' => 12, 'is_admin_response' => true, 'title' => 'Balasan Tiket', 'message' => 'Ada balasan pada tiket Anda'],
            ['type' => 'new_comment', 'post_id' => 88, 'title' => 'Komentar Baru', 'message' => 'Pengguna X mengomentari postingan Anda'],
            [
                'type' => 'trending_digest',
                'title' => 'Trending',
                'message' => 'Ringkasan post trending hari ini',
                'preview_list' => [
                    [
                        'heading' => 'Post #b626f159-7f38-4a4f-9905-5022617102f7',
                        'title' => 'Validasi Ide: Ekosistem UMKM #35',
                        'text' => 'Konten dummy untuk pengujian fitur. Inspirasi bisnis. Inspirasi bisnis.',
                    ],
                    [
                        'heading' => 'Post #a1234567-aaaa-bbbb-cccc-001122334455',
                        'title' => 'Strategi Promosi UMKM 2026',
                        'text' => 'Tips praktis untuk meningkatkan penjualan secara organik dan berbayar.',
                    ],
                ],
            ],
        ];

        $i = 0;
        foreach ($payloads as $data) {
            DatabaseNotification::create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\GenericNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => $data,
                'read_at' => ($i % 3 === 0) ? null : $now,
                'created_at' => $now->copy()->subMinutes($i * 5),
                'updated_at' => $now->copy()->subMinutes($i * 5),
            ]);
            $i++;
        }
    }
}
