<?php

namespace Database\Seeders;

use App\Models\LandingPageSection;
use App\Models\User;
use Illuminate\Database\Seeder;

class LandingPageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user as creator
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::where('email', 'admin@noteds.com')->first();
        }

        if (!$admin) {
            $this->command->warn('Admin user not found. Skipping landing page sections seeding.');
            return;
        }

        $sections = [];

        // 1. Hero Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'hero',
            'title' => 'Selamat Datang di Noteds',
            'subtitle' => 'Platform terbaik untuk membuat, berbagi, dan menjual catatan digital Anda',
            'content' => [
                'description' => 'Noteds adalah platform inovatif yang memungkinkan Anda membuat, mengelola, dan menjual catatan digital dengan mudah. Bergabunglah dengan ribuan pengguna yang telah mempercayai Noteds untuk kebutuhan catatan mereka.',
                'cta_text' => 'Mulai Sekarang',
                'cta_link' => '/register',
            ],
            'image_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200',
            'background_color' => '#3B82F6',
            'text_color' => '#FFFFFF',
            'alignment' => 'center',
            'order' => 1,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 2. Features Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'features',
            'title' => 'Fitur Unggulan',
            'subtitle' => 'Semua yang Anda butuhkan untuk mengelola catatan dengan mudah',
            'content' => [
                [
                    'icon' => '📝',
                    'title' => 'Buat Catatan dengan Mudah',
                    'description' => 'Editor yang intuitif dan powerful untuk membuat catatan yang menarik dan terorganisir.',
                ],
                [
                    'icon' => '💰',
                    'title' => 'Jual Catatan Anda',
                    'description' => 'Monetize pengetahuan Anda dengan menjual catatan ke pengguna lain di marketplace.',
                ],
                [
                    'icon' => '🔒',
                    'title' => 'Privasi & Keamanan',
                    'description' => 'Kontrol penuh atas privasi catatan Anda dengan opsi public atau private.',
                ],
                [
                    'icon' => '👥',
                    'title' => 'Kolaborasi Tim',
                    'description' => 'Buat workspace dan berkolaborasi dengan tim untuk proyek bersama.',
                ],
                [
                    'icon' => '⭐',
                    'title' => 'Catatan Unggulan',
                    'description' => 'Promosikan catatan Anda dengan fitur featured notes untuk visibility lebih tinggi.',
                ],
                [
                    'icon' => '📊',
                    'title' => 'Analytics & Insights',
                    'description' => 'Lacak performa catatan Anda dengan analytics dan insights yang detail.',
                ],
            ],
            'image_url' => null,
            'background_color' => '#F9FAFB',
            'text_color' => '#111827',
            'alignment' => 'center',
            'order' => 2,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 3. How It Works Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'how_it_works',
            'title' => 'Cara Kerja',
            'subtitle' => 'Mulai dalam 3 langkah mudah',
            'content' => [
                [
                    'number' => '1',
                    'title' => 'Daftar Akun',
                    'description' => 'Buat akun gratis dan lengkapi profil Anda dalam hitungan menit.',
                ],
                [
                    'number' => '2',
                    'title' => 'Buat Catatan',
                    'description' => 'Mulai membuat catatan pertama Anda dengan editor yang mudah digunakan.',
                ],
                [
                    'number' => '3',
                    'title' => 'Bagikan atau Jual',
                    'description' => 'Pilih untuk membagikan gratis atau menjual catatan Anda di marketplace.',
                ],
            ],
            'image_url' => null,
            'background_color' => '#FFFFFF',
            'text_color' => '#111827',
            'alignment' => 'center',
            'order' => 3,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 4. Premium Benefits Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'premium_benefits',
            'title' => 'Manfaat Premium',
            'subtitle' => 'Tingkatkan pengalaman Anda dengan paket premium',
            'content' => [
                [
                    'icon' => '✨',
                    'title' => 'Catatan Tanpa Batas',
                    'description' => 'Buat sebanyak mungkin catatan tanpa batasan.',
                ],
                [
                    'icon' => '🚀',
                    'title' => 'Auto-Approval Featured',
                    'description' => 'Request featured notes langsung aktif tanpa menunggu approval.',
                ],
                [
                    'icon' => '📈',
                    'title' => 'Analytics Lanjutan',
                    'description' => 'Akses ke analytics dan insights yang lebih detail.',
                ],
                [
                    'icon' => '💬',
                    'title' => 'Priority Support',
                    'description' => 'Dapatkan dukungan prioritas dari tim kami.',
                ],
            ],
            'image_url' => null,
            'background_color' => '#FEF3C7',
            'text_color' => '#92400E',
            'alignment' => 'center',
            'order' => 4,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 5. Trust Indicators Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'trust_indicators',
            'title' => 'Mengapa Memilih Noteds?',
            'subtitle' => 'Platform terpercaya dengan ribuan pengguna aktif',
            'content' => [
                [
                    'stat' => '10,000+',
                    'label' => 'Pengguna Aktif',
                ],
                [
                    'stat' => '50,000+',
                    'label' => 'Catatan Tersedia',
                ],
                [
                    'stat' => '5,000+',
                    'label' => 'Transaksi Sukses',
                ],
                [
                    'stat' => '99.9%',
                    'label' => 'Uptime',
                ],
            ],
            'image_url' => null,
            'background_color' => '#1F2937',
            'text_color' => '#FFFFFF',
            'alignment' => 'center',
            'order' => 5,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 6. Testimonials Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'testimonials',
            'title' => 'Apa Kata Pengguna',
            'subtitle' => 'Testimoni dari pengguna Noteds',
            'content' => [
                [
                    'quote' => 'Noteds telah mengubah cara saya mengelola catatan. Sangat mudah digunakan dan fitur marketplace-nya membantu saya menghasilkan pendapatan tambahan.',
                    'author' => 'Budi Santoso',
                    'role' => 'Content Creator',
                ],
                [
                    'quote' => 'Platform yang sempurna untuk tim kami. Kolaborasi menjadi lebih mudah dengan fitur workspace yang lengkap.',
                    'author' => 'Siti Nurhaliza',
                    'role' => 'Project Manager',
                ],
                [
                    'quote' => 'Sebagai mahasiswa, Noteds membantu saya mengorganisir semua catatan kuliah. Dan saya juga bisa menjual catatan saya ke teman-teman!',
                    'author' => 'Ahmad Fauzi',
                    'role' => 'Mahasiswa',
                ],
            ],
            'image_url' => null,
            'background_color' => '#F3F4F6',
            'text_color' => '#111827',
            'alignment' => 'center',
            'order' => 6,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // 7. Promo Section (Active)
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'promo',
            'title' => '🎉 Promo Spesial Bulan Ini!',
            'subtitle' => 'Dapatkan diskon 50% untuk paket premium',
            'content' => [
                'description' => 'Upgrade ke paket premium sekarang dan dapatkan akses ke semua fitur premium dengan harga spesial. Promo terbatas!',
                'cta_text' => 'Dapatkan Sekarang',
                'cta_link' => '/subscription/create',
                'discount' => '50%',
            ],
            'image_url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800',
            'background_color' => '#EF4444',
            'text_color' => '#FFFFFF',
            'alignment' => 'center',
            'order' => 7,
            'is_active' => true,
            'valid_from' => now()->subDays(5)->toDateString(),
            'valid_until' => now()->addDays(25)->toDateString(),
        ];

        // 8. Custom Section
        $sections[] = [
            'created_by' => $admin->id,
            'section_type' => 'custom',
            'title' => 'Bergabunglah dengan Komunitas Noteds',
            'subtitle' => 'Dapatkan update terbaru dan tips dari komunitas',
            'content' => [
                'description' => 'Ikuti kami di media sosial untuk mendapatkan update terbaru, tips & trik, dan berinteraksi dengan komunitas Noteds.',
                'social_links' => [
                    ['platform' => 'Instagram', 'url' => 'https://instagram.com/noteds'],
                    ['platform' => 'Twitter', 'url' => 'https://twitter.com/noteds'],
                    ['platform' => 'Facebook', 'url' => 'https://facebook.com/noteds'],
                ],
            ],
            'image_url' => null,
            'background_color' => '#ECFDF5',
            'text_color' => '#065F46',
            'alignment' => 'center',
            'order' => 8,
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];

        // Create sections
        $createdCount = 0;
        foreach ($sections as $sectionData) {
            LandingPageSection::updateOrCreate(
                [
                    'section_type' => $sectionData['section_type'],
                    'order' => $sectionData['order'],
                ],
                $sectionData
            );
            $createdCount++;
        }

        $this->command->info("Created/Updated {$createdCount} landing page sections.");
    }
}

