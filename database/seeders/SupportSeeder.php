<?php

namespace Database\Seeders;

use App\Models\AdminActionLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SupportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        $sellers = User::role('seller')->get();
        $buyers = User::role('buyer')->get();

        if ($buyers->isEmpty() || !$admin) {
            return;
        }

        $this->seedSupportTickets($buyers, $admin, $sellers);
        $this->seedAdminLogs($admin, $sellers);
    }

    protected function seedSupportTickets($buyers, User $admin, $sellers): void
    {
        foreach ($buyers->take(6) as $index => $buyer) {
            $status = Arr::random(['open', 'in_progress', 'resolved', 'closed']);
            $ticket = SupportTicket::updateOrCreate(
                [
                    'user_id' => $buyer->id,
                    'title' => 'Permintaan bantuan #' . ($index + 1),
                ],
                [
                    'description' => 'Simulasi tiket dukungan untuk menguji alur bantuan pengguna dan dashboard admin.',
                    'status' => $status,
                    'priority' => Arr::random(['low', 'medium', 'high']),
                    'assigned_to' => $admin->id,
                    'screenshots' => ['support/sample-ticket-' . ($index + 1) . '.png'],
                    'links' => ['https://noteds.test/tickets/' . ($index + 1)],
                    'admin_response' => $status !== 'open' ? 'Kami sudah meninjau tiket kamu, mohon cek notifikasi terbaru.' : null,
                    'closed_by' => in_array($status, ['resolved', 'closed'], true) ? $admin->id : null,
                    'closed_at' => in_array($status, ['resolved', 'closed'], true) ? now()->subHours(rand(3, 24)) : null,
                ]
            );

            if ($ticket->replies()->exists()) {
                continue;
            }

            $replies = [
                [
                    'user_id' => $buyer->id,
                    'message' => 'Halo kak, saya butuh bantuan terkait transaksi terakhir.',
                    'is_admin' => false,
                    'created_at' => now()->subHours(8),
                ],
                [
                    'user_id' => $admin->id,
                    'message' => 'Hai! Kami sudah cek dan saldo kamu aman. Coba refresh halaman dompet ya.',
                    'is_admin' => true,
                    'created_at' => now()->subHours(7)->addMinutes(10),
                ],
            ];

            foreach ($replies as $payload) {
                SupportTicketReply::create(array_merge($payload, [
                    'support_ticket_id' => $ticket->id,
                ]));
            }
        }
    }

    protected function seedAdminLogs(User $admin, $sellers): void
    {
        if ($sellers->isEmpty()) {
            return;
        }

        foreach ($sellers->take(4) as $seller) {
            AdminActionLog::updateOrCreate(
                [
                    'admin_id' => $admin->id,
                    'target_user_id' => $seller->id,
                    'action' => 'review_seller_content',
                ],
                [
                    'reason' => 'Peninjauan rutin konten marketplace.',
                    'metadata' => [
                        'notes_count' => $seller->notes()->count(),
                        'violations_detected' => false,
                    ],
                ]
            );
        }
    }
}


