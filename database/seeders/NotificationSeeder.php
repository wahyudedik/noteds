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
            ['type' => 'new_order', 'order_id' => 123, 'order_number' => 'ORD-20260124-ABCD', 'title' => 'Pesanan Baru', 'message' => 'Order #ORD-20260124-ABCD dibuat'],
            ['type' => 'order_status_update', 'order_id' => 123, 'status' => 'completed', 'title' => 'Status Order Diperbarui', 'message' => 'Order #ORD-20260124-ABCD → completed'],
            ['type' => 'payment_failed', 'order_id' => 124, 'failure_reason' => 'deny', 'title' => 'Pembayaran Gagal', 'message' => 'Transaksi gagal, silakan coba lagi'],
            ['type' => 'withdrawal_status', 'withdrawal_id' => 99, 'status' => 'approved', 'user_type' => 'seller', 'title' => 'Status Penarikan', 'message' => 'Penarikan Rp 250.000 → approved'],
            ['type' => 'product_approved', 'product_id' => 45, 'title' => 'Produk Disetujui', 'message' => 'Produk Anda telah disetujui'],
            ['type' => 'product_rejected', 'product_id' => 46, 'reason' => 'incomplete_description', 'title' => 'Produk Ditolak', 'message' => 'Produk ditolak: deskripsi tidak lengkap'],
            ['type' => 'new_campaign', 'campaign_id' => 77, 'title' => 'Kampanye Baru', 'message' => 'Kampanye tersedia untuk clippers'],
            ['type' => 'clip_approved', 'clip_id' => 555, 'title' => 'Clip Disetujui', 'message' => 'Clip Anda telah disetujui'],
            ['type' => 'brand_approved', 'title' => 'Brand Disetujui', 'message' => 'Pendaftaran brand Anda disetujui'],
            ['type' => 'support_ticket_response', 'ticket_id' => 12, 'is_admin_response' => true, 'title' => 'Balasan Tiket', 'message' => 'Ada balasan pada tiket Anda'],
            ['type' => 'new_comment', 'post_id' => 88, 'title' => 'Komentar Baru', 'message' => 'Pengguna X mengomentari postingan Anda'],
            ['type' => 'topup_success', 'top_up_id' => 999, 'amount' => 100000, 'title' => 'Top Up Berhasil', 'message' => 'Wallet bertambah Rp 100.000'],
            ['type' => 'webhook_failed', 'order_id' => 125, 'transaction_status' => 'pending', 'error' => 'timeout', 'title' => 'Webhook Order Failed', 'message' => 'Order webhook processing failed permanently. Check logs.'],
            [
                'type' => 'trending_digest',
                'title' => 'Trending',
                'message' => 'Ringkasan post trending hari ini',
                'preview_list' => [
                    [
                        'heading' => 'Post #b626f159-7f38-4a4f-9905-5022617102f7',
                        'title' => 'Validasi Ide: Marketplace Lokal #35',
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
