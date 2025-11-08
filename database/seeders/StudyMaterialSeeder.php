<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\StudyMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class StudyMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $notes = Note::with('user')->take(12)->get();

        foreach ($notes as $note) {
            $owner = $note->user;

            if (!$owner) {
                continue;
            }

            foreach ($this->materialTemplates($note->title) as $material) {
                StudyMaterial::updateOrCreate(
                    [
                        'user_id' => $owner->id,
                        'note_id' => $note->id,
                        'type' => $material['type'],
                    ],
                    [
                        'title' => $material['title'],
                        'content' => $material['content'],
                        'item_count' => $material['item_count'],
                    ]
                );
            }
        }
    }

    protected function materialTemplates(string $noteTitle): array
    {
        $cleanTitle = trim(Arr::first(explode('-', $noteTitle)) ?? $noteTitle);

        return [
            [
                'type' => 'flashcards',
                'title' => "Flashcards: {$cleanTitle}",
                'item_count' => 3,
                'content' => [
                    ['question' => 'Apa tujuan utama catatan ini?', 'answer' => 'Memberikan panduan praktis untuk implementasi.'],
                    ['question' => 'Sebutkan tools utama yang digunakan.', 'answer' => 'Laravel, Tailwind, dan Midtrans.'],
                    ['question' => 'Bagaimana cara mengukur keberhasilan materi ini?', 'answer' => 'Dengan melihat konversi dan feedback user.'],
                ],
            ],
            [
                'type' => 'study_guide',
                'title' => "Study Guide: {$cleanTitle}",
                'item_count' => 4,
                'content' => [
                    'Langkah 1: Baca ringkasan untuk memahami konteks.',
                    'Langkah 2: Pelajari studi kasus yang disediakan.',
                    'Langkah 3: Terapkan pada proyek kecil untuk validasi.',
                    'Langkah 4: Catat insight dan bagikan di komunitas.',
                ],
            ],
        ];
    }
}


