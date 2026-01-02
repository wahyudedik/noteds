<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => '10 Strategi Marketing Digital untuk Bisnis Kecil di 2024',
                'description' => 'Pelajari strategi marketing digital yang efektif dan terjangkau untuk mengembangkan bisnis kecil Anda di era digital. Dari social media marketing hingga content marketing.',
                'url' => 'https://noteds.com/articles/marketing-digital-bisnis-kecil',
                'source' => 'Noteds Editorial',
                'category' => 'Marketing',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(2),
                'language' => 'id',
            ],
            [
                'title' => 'Cara Membuat Business Plan yang Menarik Investor',
                'description' => 'Panduan lengkap membuat business plan yang profesional dan menarik perhatian investor. Termasuk template dan contoh yang bisa Anda gunakan.',
                'url' => 'https://noteds.com/articles/business-plan-investor',
                'source' => 'Noteds Editorial',
                'category' => 'Strategy',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(5),
                'language' => 'id',
            ],
            [
                'title' => 'Tips Mengelola Keuangan Startup di Tahun Pertama',
                'description' => 'Pelajari cara mengelola keuangan startup dengan baik sejak tahun pertama. Hindari kesalahan umum yang sering dilakukan founder startup.',
                'url' => 'https://noteds.com/articles/keuangan-startup-tahun-pertama',
                'source' => 'Noteds Editorial',
                'category' => 'Finance',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(7),
                'language' => 'id',
            ],
            [
                'title' => 'Teknologi AI untuk Meningkatkan Produktivitas Bisnis',
                'description' => 'Eksplorasi berbagai tools AI yang bisa membantu meningkatkan produktivitas bisnis Anda. Dari ChatGPT hingga automation tools.',
                'url' => 'https://noteds.com/articles/ai-produktivitas-bisnis',
                'source' => 'Noteds Editorial',
                'category' => 'Technology',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(10),
                'language' => 'id',
            ],
            [
                'title' => 'Membangun Brand Identity yang Kuat untuk Startup',
                'description' => 'Panduan membangun brand identity yang konsisten dan memorable. Pelajari elemen-elemen penting dalam branding.',
                'url' => 'https://noteds.com/articles/brand-identity-startup',
                'source' => 'Noteds Editorial',
                'category' => 'Marketing',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(12),
                'language' => 'id',
            ],
            [
                'title' => 'Strategi Customer Acquisition untuk E-commerce',
                'description' => 'Pelajari berbagai strategi untuk mendapatkan customer baru di bisnis e-commerce. Dari SEO hingga paid advertising.',
                'url' => 'https://noteds.com/articles/customer-acquisition-ecommerce',
                'source' => 'Noteds Editorial',
                'category' => 'Marketing',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(15),
                'language' => 'id',
            ],
            [
                'title' => 'Cara Mencari Co-founder yang Tepat untuk Startup',
                'description' => 'Tips menemukan dan memilih co-founder yang cocok dengan visi dan nilai-nilai Anda. Hindari kesalahan dalam memilih partner bisnis.',
                'url' => 'https://noteds.com/articles/mencari-cofounder',
                'source' => 'Noteds Editorial',
                'category' => 'Startup',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(18),
                'language' => 'id',
            ],
            [
                'title' => 'Pentingnya Data Analytics dalam Pengambilan Keputusan Bisnis',
                'description' => 'Pelajari bagaimana data analytics dapat membantu Anda membuat keputusan bisnis yang lebih baik dan berbasis data.',
                'url' => 'https://noteds.com/articles/data-analytics-bisnis',
                'source' => 'Noteds Editorial',
                'category' => 'Strategy',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(20),
                'language' => 'id',
            ],
            [
                'title' => 'Mengoptimalkan Cash Flow untuk Bisnis yang Sehat',
                'description' => 'Strategi mengelola cash flow dengan baik agar bisnis tetap sehat dan bisa berkembang. Hindari masalah likuiditas.',
                'url' => 'https://noteds.com/articles/optimasi-cash-flow',
                'source' => 'Noteds Editorial',
                'category' => 'Finance',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(22),
                'language' => 'id',
            ],
            [
                'title' => 'Leveraging Social Media untuk Brand Awareness',
                'description' => 'Cara efektif menggunakan social media untuk meningkatkan brand awareness dan engagement dengan target audience Anda.',
                'url' => 'https://noteds.com/articles/social-media-brand-awareness',
                'source' => 'Noteds Editorial',
                'category' => 'Marketing',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(25),
                'language' => 'id',
            ],
            [
                'title' => 'Building a Scalable Business Model',
                'description' => 'Learn how to design a business model that can scale efficiently as your startup grows. Key principles and frameworks.',
                'url' => 'https://noteds.com/articles/scalable-business-model',
                'source' => 'Noteds Editorial',
                'category' => 'Strategy',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(28),
                'language' => 'en',
            ],
            [
                'title' => 'Essential Tools for Remote Team Management',
                'description' => 'Discover the best tools and practices for managing remote teams effectively. From communication to project management.',
                'url' => 'https://noteds.com/articles/remote-team-management',
                'source' => 'Noteds Editorial',
                'category' => 'Technology',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(30),
                'language' => 'en',
            ],
            [
                'title' => 'Fundraising Strategies for Early-Stage Startups',
                'description' => 'A comprehensive guide to fundraising for early-stage startups. Learn about different funding options and how to pitch to investors.',
                'url' => 'https://noteds.com/articles/fundraising-early-stage',
                'source' => 'Noteds Editorial',
                'category' => 'Finance',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(32),
                'language' => 'en',
            ],
            [
                'title' => 'Customer Retention Strategies That Work',
                'description' => 'Proven strategies to retain customers and reduce churn. Learn how to build long-term relationships with your customers.',
                'url' => 'https://noteds.com/articles/customer-retention-strategies',
                'source' => 'Noteds Editorial',
                'category' => 'Marketing',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(35),
                'language' => 'en',
            ],
            [
                'title' => 'The Future of E-commerce: Trends to Watch',
                'description' => 'Explore emerging trends in e-commerce that will shape the future of online retail. Stay ahead of the competition.',
                'url' => 'https://noteds.com/articles/future-ecommerce-trends',
                'source' => 'Noteds Editorial',
                'category' => 'Technology',
                'author' => 'Noteds Team',
                'published_at' => now()->subDays(38),
                'language' => 'en',
            ],
        ];

        foreach ($articles as $articleData) {
            $articleData['url_hash'] = hash('sha256', $articleData['url']);
            $articleData['fetched_at'] = now();
            
            Article::updateOrCreate(
                ['url_hash' => $articleData['url_hash']],
                $articleData
            );
        }

        $this->command->info('Seeded ' . count($articles) . ' articles.');
    }
}
