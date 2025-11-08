<?php

namespace Database\Seeders;

use App\Models\Documentation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();

        if (!$admin) {
            return;
        }

        foreach ($this->documents() as $doc) {
            Documentation::updateOrCreate(
                [
                    'slug' => $doc['slug'],
                ],
                array_merge($doc, ['created_by' => $admin->id])
            );
        }
    }

    protected function documents(): array
    {
        return [
            [
                'slug' => 'getting-started-marketplace',
                'title' => 'Mulai Jual Catatan di Noteds',
                'content' => '<p>Panduan langkah demi langkah membuat catatan, mengatur harga, dan mempublikasikan ke marketplace.</p>',
                'summary' => 'Panduan ringkas memulai penjualan catatan digital.',
                'category' => 'wiki',
                'icon' => 'heroicons-outline:document-text',
                'links' => ['/docs/publish-note', '/docs/pricing-guide'],
                'screenshots' => ['docs/screenshots/create-note.png'],
                'video_urls' => ['https://www.youtube.com/watch?v=dummy123'],
                'tags' => ['marketplace', 'seller', 'pricing'],
                'order' => 1,
                'is_active' => true,
                'view_count' => 135,
                'helpful_count' => 42,
            ],
            [
                'slug' => 'wallet-and-withdraw',
                'title' => 'Kelola Dompet & Withdraw',
                'content' => '<p>Pelajari cara top up saldo, melihat riwayat transaksi, hingga menarik dana ke rekening bank.</p>',
                'summary' => 'Semua hal terkait dompet digital Noteds.',
                'category' => 'screenshot_guide',
                'icon' => 'heroicons-outline:currency-dollar',
                'links' => ['/wallet', '/docs/withdraw-policy'],
                'screenshots' => ['docs/screenshots/wallet-dashboard.png'],
                'video_urls' => [],
                'tags' => ['wallet', 'withdraw', 'finance'],
                'order' => 2,
                'is_active' => true,
                'view_count' => 98,
                'helpful_count' => 30,
            ],
            [
                'slug' => 'ai-tools-overview',
                'title' => 'Eksplorasi Fitur AI Noteds',
                'content' => '<p>Kenali bagaimana AI membantu membuat ringkasan, keyword, hingga rekomendasi harga secara otomatis.</p>',
                'summary' => 'Overview modul AI untuk penjual dan pembeli.',
                'category' => 'video_tutorial',
                'icon' => 'heroicons-outline:sparkles',
                'links' => ['/docs/ai-summary', '/docs/ai-tagging'],
                'screenshots' => [],
                'video_urls' => ['https://www.youtube.com/watch?v=dummy456'],
                'tags' => ['ai', 'automation', 'insight'],
                'order' => 3,
                'is_active' => true,
                'view_count' => 77,
                'helpful_count' => 21,
            ],
        ];
    }
}


