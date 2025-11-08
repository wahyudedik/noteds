<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\NoteReport;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class NoteReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        $buyers = User::role('buyer')->get();

        if ($buyers->isEmpty()) {
            return;
        }

        $this->seedNoteReports($buyers, $admin);
        $this->seedUserReports($buyers, $admin);
    }

    protected function seedNoteReports($buyers, ?User $admin): void
    {
        $notes = Note::with('user')->take(12)->get();

        foreach ($notes as $note) {
            $reporter = $buyers->random();

            if ($note->user_id === $reporter->id) {
                continue;
            }

            $status = Arr::random(['pending', 'reviewed', 'resolved', 'dismissed']);

            NoteReport::updateOrCreate(
                [
                    'note_id' => $note->id,
                    'user_id' => $reporter->id,
                ],
                [
                    'reason' => Arr::random(['spam', 'inappropriate', 'fraud', 'other']),
                    'description' => 'Laporan otomatis untuk uji fitur moderasi catatan dan notifikasi admin.',
                    'status' => $status,
                    'reviewed_by' => $status === 'pending' ? null : $admin?->id,
                    'reviewed_at' => $status === 'pending' ? null : now()->subDays(rand(0, 5)),
                    'admin_notes' => $status === 'resolved' ? 'Konten diperbaiki oleh penjual.' : null,
                ]
            );
        }
    }

    protected function seedUserReports($buyers, ?User $admin): void
    {
        $sellers = User::role('seller')->get();

        if ($sellers->isEmpty()) {
            return;
        }

        foreach ($buyers->take(8) as $buyer) {
            $target = $sellers->random();

            if ($target->id === $buyer->id) {
                continue;
            }

            $status = Arr::random(['pending', 'reviewed', 'resolved', 'dismissed']);

            UserReport::updateOrCreate(
                [
                    'reported_user_id' => $target->id,
                    'user_id' => $buyer->id,
                ],
                [
                    'reason' => Arr::random(['spam', 'harassment', 'impersonation', 'other']),
                    'description' => 'Umpan data untuk dasbor moderasi akun pengguna.',
                    'status' => $status,
                    'reviewed_by' => $status === 'pending' ? null : $admin?->id,
                    'reviewed_at' => $status === 'pending' ? null : now()->subDays(rand(1, 4)),
                    'admin_notes' => $status === 'dismissed' ? 'Tidak ditemukan pelanggaran.' : null,
                ]
            );
        }
    }
}


