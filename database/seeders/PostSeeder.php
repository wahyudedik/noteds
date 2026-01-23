<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $titles = [
            'Cara Memulai Startup dengan Modal Minim',
            'Pengalaman Scaling Aplikasi Laravel ke 1 Juta Pengguna',
            'Tips Marketing Organik untuk Produk Digital',
            'Mencari Partner untuk Proyek SaaS',
            'Tool Rekomendasi untuk Workflow Desain',
            'Validasi Ide: Marketplace Lokal'
        ];
        $purposes = ['idea_business','share_experience','ask_question','find_partner','find_tools','validate_idea'];

        foreach (range(1, 40) as $i) {
            $user = $users->random();
            Post::firstOrCreate(
                ['title' => $titles[$i % count($titles)] . " #{$i}", 'user_id' => $user->id],
                [
                    'purpose_type' => $purposes[$i % count($purposes)],
                    'business_type' => $i % 3 === 0 ? 'Technology' : 'General',
                    'content' => 'Konten dummy untuk pengujian fitur. ' . str_repeat('Inspirasi bisnis. ', rand(3,6)),
                    'upvotes_count' => rand(0, 50),
                    'downvotes_count' => rand(0, 20),
                    'comments_count' => rand(0, 30),
                    'reposts_count' => rand(0, 15),
                    'status' => 'active',
                    'publish_status' => 'published',
                ]
            );
        }
    }
}
