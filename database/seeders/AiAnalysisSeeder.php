<?php

namespace Database\Seeders;

use App\Models\AiAnalysis;
use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AiAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        $notes = Note::with('user')->take(15)->get();

        foreach ($notes as $note) {
            $owner = $note->user;

            if (!$owner) {
                continue;
            }

            AiAnalysis::updateOrCreate(
                [
                    'note_id' => $note->id,
                    'user_id' => $owner->id,
                    'analysis_type' => Arr::random(['analyzer', 'qa', 'comparison', 'extractor']),
                ],
                [
                    'summary' => 'Ringkasan otomatis yang menyoroti poin penting catatan dan rekomendasi tindak lanjut.',
                    'key_points' => [
                        'Topik utama: ' . Arr::random(['Laravel 12', 'Monetisasi catatan', 'Strategi AI']),
                        'Durasi belajar ideal: ' . rand(15, 45) . ' menit',
                        'Tingkat kesulitan: ' . Arr::random(['Beginner', 'Intermediate', 'Advanced']),
                    ],
                    'insights' => [
                        'Insight' => 'Pengguna disarankan menerapkan contoh studi kasus untuk hasil maksimal.',
                        'Action' => 'Tambahkan file pendukung agar nilai jual meningkat.',
                    ],
                    'topics' => Arr::random([
                        ['Laravel', 'Marketplace', 'Workflow'],
                        ['AI', 'Productivity', 'Automation'],
                        ['Marketing', 'Pricing', 'Conversion'],
                    ]),
                    'difficulty_level' => Arr::random(['beginner', 'intermediate', 'advanced']),
                    'estimated_time_minutes' => rand(20, 60),
                    'metadata' => [
                        'generated_at' => now()->toDateTimeString(),
                        'model' => 'ollama/noteds-mini',
                        'confidence' => Arr::random([0.82, 0.9, 0.95]),
                    ],
                ]
            );
        }
    }
}


