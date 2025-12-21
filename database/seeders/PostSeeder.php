<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $purposeTypes = [
            'idea_business',
            'ask_question',
            'share_experience',
            'find_partner',
            'find_tools',
            'validate_idea',
        ];

        $posts = [
            // Idea Business Posts
            [
                'purpose_type' => 'idea_business',
                'title' => 'Ide Bisnis: Platform Marketplace untuk UMKM Lokal',
                'content' => 'Saya punya ide untuk membuat platform marketplace yang khusus untuk UMKM lokal. Platform ini akan membantu UMKM menjual produk mereka secara online dengan biaya yang terjangkau. Fitur utama termasuk sistem rating, review, dan integrasi dengan kurir lokal. Apakah ada yang tertarik untuk berkolaborasi?',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'idea_business',
                'title' => 'Startup Idea: Aplikasi Manajemen Keuangan untuk Freelancer',
                'content' => 'Sebagai freelancer, saya sering kesulitan mengelola keuangan. Saya punya ide untuk membuat aplikasi yang membantu freelancer tracking income, expenses, dan tax calculation secara otomatis. Aplikasi ini juga bisa generate invoice dan reminder untuk pembayaran.',
                'is_validated_post' => false,
            ],

            // Ask Question Posts
            [
                'purpose_type' => 'ask_question',
                'title' => 'Bagaimana Cara Mencari Investor untuk Startup?',
                'content' => 'Saya sedang mencari investor untuk startup saya di bidang fintech. Startup sudah memiliki MVP dan beberapa early users. Bagaimana cara terbaik untuk approach investor? Apakah ada platform atau event yang recommended untuk networking dengan investor?',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'ask_question',
                'title' => 'Tools Apa yang Paling Efektif untuk Digital Marketing?',
                'content' => 'Saya baru mulai bisnis online dan ingin tahu tools apa saja yang paling efektif untuk digital marketing dengan budget terbatas. Apakah ada rekomendasi tools untuk social media management, email marketing, dan analytics yang free atau affordable?',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'ask_question',
                'title' => 'Bagaimana Strategi Pricing untuk SaaS Product?',
                'content' => 'Saya sedang mengembangkan SaaS product dan bingung menentukan pricing strategy. Bagaimana cara menentukan harga yang tepat? Apakah lebih baik pakai freemium model atau langsung paid? Ada yang punya pengalaman atau tips?',
                'is_validated_post' => false,
            ],

            // Share Experience Posts
            [
                'purpose_type' => 'share_experience',
                'title' => 'Pengalaman Launching Produk Pertama Kali',
                'content' => 'Baru saja launching produk pertama saya dan ingin share pengalaman. Prosesnya tidak mudah, banyak trial and error. Yang paling penting adalah validate idea dulu sebelum build. Saya melakukan customer interviews, MVP testing, dan iterasi berdasarkan feedback. Hasilnya, produk mendapat response positif dari early adopters.',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'share_experience',
                'title' => 'Kesalahan yang Sering Dilakukan Saat Starting Business',
                'content' => 'Setelah 3 tahun menjalankan bisnis, saya ingin share beberapa kesalahan yang sering dilakukan pemula: 1) Tidak melakukan market research, 2) Terlalu fokus pada produk tanpa memikirkan marketing, 3) Tidak memiliki financial planning yang jelas. Semoga bisa membantu yang baru mulai!',
                'is_validated_post' => false,
            ],

            // Find Partner Posts
            [
                'purpose_type' => 'find_partner',
                'title' => 'Mencari Tech Co-Founder untuk EdTech Startup',
                'content' => 'Saya sedang mencari tech co-founder untuk startup di bidang education technology. Saya memiliki background di education dan business development, tapi butuh partner yang expert di software development (preferably Laravel/Vue.js). Startup sudah memiliki concept dan beberapa early customers. Tertarik untuk diskusi lebih lanjut?',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'find_partner',
                'title' => 'Cari Business Partner untuk E-Commerce Business',
                'content' => 'Saya punya e-commerce business yang sudah running dan ingin expand. Mencari business partner yang memiliki expertise di supply chain dan operations. Business sudah profitable dan memiliki growth potential yang besar. Open untuk equity partnership.',
                'is_validated_post' => false,
            ],

            // Find Tools Posts
            [
                'purpose_type' => 'find_tools',
                'title' => 'Rekomendasi Project Management Tools untuk Remote Team',
                'content' => 'Tim saya bekerja remote dan butuh project management tool yang efektif. Saat ini pakai Trello tapi kurang fit untuk kebutuhan. Ada yang bisa rekomendasikan tools yang bagus untuk remote team? Budget terbatas jadi prefer yang free atau affordable.',
                'is_validated_post' => false,
            ],
            [
                'purpose_type' => 'find_tools',
                'title' => 'Tools untuk Customer Support yang Recommended?',
                'content' => 'Bisnis saya mulai berkembang dan butuh sistem customer support yang lebih terorganisir. Saat ini masih pakai email manual. Ada yang punya rekomendasi tools untuk customer support? Fitur yang dibutuhkan: ticket system, live chat, dan knowledge base.',
                'is_validated_post' => false,
            ],

            // Validate Idea Posts
            [
                'purpose_type' => 'validate_idea',
                'title' => 'Validasi Ide: Platform Crowdfunding untuk Startup Lokal',
                'content' => 'Saya punya ide untuk membuat platform crowdfunding khusus untuk startup lokal di Indonesia. Platform akan membantu startup mendapatkan funding dari community dengan sistem reward-based. Target market adalah startup early stage yang kesulitan akses ke traditional funding. Mohon feedback dan validasi dari komunitas!',
                'is_validated_post' => true,
            ],
            [
                'purpose_type' => 'validate_idea',
                'title' => 'Validasi Ide: Aplikasi Meal Planning untuk Health Conscious',
                'content' => 'Ide aplikasi yang membantu user meal planning dengan fokus pada healthy eating. Aplikasi akan provide meal plans, shopping lists, dan nutrition tracking. Target market adalah health conscious individuals dan people dengan dietary restrictions. Apakah ide ini layak untuk dikembangkan?',
                'is_validated_post' => true,
            ],
            [
                'purpose_type' => 'validate_idea',
                'title' => 'Validasi Ide: Marketplace untuk Jasa Freelance Lokal',
                'content' => 'Platform marketplace yang menghubungkan freelancer lokal dengan clients. Fokus pada quality control dan fair pricing. Platform akan take commission yang reasonable dan provide support untuk both sides. Apakah ada market untuk ini?',
                'is_validated_post' => true,
            ],
        ];

        foreach ($posts as $index => $postData) {
            $user = $users->random();
            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));

            Post::create([
                'user_id' => $user->id,
                'purpose_type' => $postData['purpose_type'],
                'title' => $postData['title'],
                'content' => $postData['content'],
                'is_validated_post' => $postData['is_validated_post'],
                'upvotes_count' => rand(0, 25),
                'downvotes_count' => rand(0, 5),
                'comments_count' => rand(0, 15),
                'status' => 'active',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create additional random posts
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $purposeType = $purposeTypes[array_rand($purposeTypes)];
            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));

            $titles = [
                'idea_business' => ['Ide Bisnis Baru', 'Startup Idea', 'Business Concept'],
                'ask_question' => ['Pertanyaan tentang', 'Bagaimana cara', 'Tips untuk'],
                'share_experience' => ['Pengalaman', 'Lessons Learned', 'Sharing'],
                'find_partner' => ['Mencari Partner', 'Cari Co-Founder', 'Looking for Partner'],
                'find_tools' => ['Rekomendasi Tools', 'Cari Tools', 'Best Tools for'],
                'validate_idea' => ['Validasi Ide', 'Mohon Feedback', 'Apakah Ide Ini Layak'],
            ];

            $titlePrefix = $titles[$purposeType][array_rand($titles[$purposeType])];

            Post::create([
                'user_id' => $user->id,
                'purpose_type' => $purposeType,
                'title' => $titlePrefix . ' ' . fake()->words(3, true),
                'content' => fake()->paragraphs(rand(2, 4), true),
                'is_validated_post' => $purposeType === 'validate_idea',
                'upvotes_count' => rand(0, 20),
                'downvotes_count' => rand(0, 3),
                'comments_count' => rand(0, 10),
                'status' => 'active',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
