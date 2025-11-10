<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Folder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sellers to create notes for them
        $sellers = User::role('seller')->get();
        $tags = Tag::all();

        if ($sellers->isEmpty()) {
            return;
        }

        // Create sample content for notes
        $noteTemplates = [
            [
                'title' => 'Complete Laravel 12 Guide',
                'content' => <<<'TEXT'
Pelajari Laravel 12 dari nol hingga mahir dengan panduan komprehensif yang mencakup seluruh ekosistem framework terbaru ini. Modul pertama membahas konsep dasar seperti struktur proyek, konfigurasi environment, dan pengelolaan dependency menggunakan Composer. Anda juga akan mempelajari perubahan signifikan pada lifecycle request, optimasi route caching, serta fitur baru seperti flag artisan untuk debugging dan command scheduling berbasis kondisi.

Bagian menengah mengeksplorasi praktik terbaik dalam Eloquent ORM, termasuk eager loading lanjutan, dynamic relationship, dan pemetaan data ke DTO. Disertai studi kasus lengkap membangun modul marketplace dengan filter pencarian, pagination kustom, serta pengelolaan transaksi menggunakan database transaction dan event listener.

Pada modul akhir, Anda akan diajak mengimplementasikan stack modern: broadcasting dengan WebSockets, queue worker terdistribusi, hingga testing otomatis dengan Pest. Setiap bab dilengkapi checklist kesiapan produksi, snippet konfigurasi, dan tips deployment ke berbagai platform seperti Laravel Forge, Vapor, maupun Docker Swarm.
TEXT,
                'price' => 99000,
            ],
            [
                'title' => 'Advanced PHP Design Patterns',
                'content' => <<<'TEXT'
Materi ini menyelami lebih dalam pola desain yang relevan untuk aplikasi PHP modern. Dimulai dengan review prinsip SOLID, dependency inversion, dan bagaimana mengidentifikasi bau kode yang biasanya menjadi indikasi perlunya refactor ke pola tertentu. Contoh kasus menggunakan aplikasi pembayaran mikro yang terus berevolusi sehingga pembaca memahami kapan harus memilih Strategy dibandingkan State, serta bagaimana menerapkan Composite untuk mengelola struktur data hierarkis.

Setiap pola disajikan dengan diagram UML sederhana, implementasi kode nyata, dan variasi penerapan saat menggunakan framework populer seperti Laravel atau Symfony. Pembahasan juga mencakup integrasi dengan dependency injection container, membangun pipeline middleware, serta memadukan pola Observer dengan event bus internal.

Sebagai penutup, tersedia bab khusus mengenai architectural patterns seperti Hexagonal Architecture dan CQRS. Anda akan mempelajari bagaimana memisahkan domain logic dari lapisan infrastruktur, menyusun test suite yang dapat bertahan dalam jangka panjang, dan menyiapkan strategi migrasi bertahap dari aplikasi monolit ke service-oriented architecture tanpa downtime berarti.
TEXT,
                'price' => 125000,
            ],
            [
                'title' => 'Mastering MySQL Performance',
                'content' => <<<'TEXT'
Dokumen teknis ini dirancang bagi developer dan DBA yang ingin memaksimalkan performa MySQL di lingkungan produksi. Bab pembuka mengulas arsitektur InnoDB, buffer pool, dan bagaimana memahami output dari `SHOW ENGINE INNODB STATUS`. Anda akan belajar membaca query execution plan, mengidentifikasi bottleneck, serta menggunakan histogram dan invisible index untuk memandu optimasi.

Terdapat panduan langkah demi langkah membuat strategi indexing untuk workload transaksi maupun analitik. Setiap strategi dilengkapi dataset percobaan sehingga pembaca bisa membandingkan hasil benchmark sebelum dan sesudah optimasi. Selain itu, materi turut membahas query rewrite, penggunaan Common Table Expressions, dan teknik batching data besar secara efisien.

Bagian akhir fokus pada pemeliharaan server: konfigurasi MySQL 8, replikasi semi-synchronous, pemanfaatan read replica, serta backup incremental menggunakan Percona XtraBackup. Penjelasan disertai checklist monitoring menggunakan Prometheus dan Grafana agar performa tetap terjaga walau trafik meningkat drastis.
TEXT,
                'price' => 150000,
            ],
            [
                'title' => 'Building RESTful APIs',
                'content' => <<<'TEXT'
E-book ini menuntun Anda membangun RESTful API yang kokoh dan mudah dikembangkan. Dimulai dari perancangan kontrak API menggunakan OpenAPI Specification, mendokumentasikan endpoint, serta mengatur versioning agar kompatibilitas terjaga. Anda akan belajar membangun lapisan service dan repository yang bersih, mengelola validasi request kompleks, serta menerapkan policy dan gate untuk kontrol akses.

Contoh kode fokus pada Laravel sebagai backend utama, termasuk bagaimana memanfaatkan Resources untuk serialisasi data yang konsisten, menerapkan rate limiting adaptif, dan mengintegrasikan fitur caching menggunakan `Cache::tags`. Setiap bab dilengkapi latihan membuat endpoint CRUD, pencarian dengan parameter dinamis, dan webhook event.

Bab terakhir mengeksplorasi pengujian otomatis dan deployment. Anda akan menyiapkan suite feature test, kontrak test menggunakan Postman/Newman, serta pipeline CI/CD yang melakukan static analysis, security scan, dan rolling deployment. Tersedia pula panduan menambahkan observabilitas melalui log terstruktur, tracing, dan health check endpoint.
TEXT,
                'price' => 110000,
            ],
            [
                'title' => 'JavaScript ES6+ Fundamentals',
                'content' => <<<'TEXT'
Materi ini memberikan fondasi kuat untuk JavaScript modern. Bagian pertama meninjau ulang konsep inti seperti scope, closure, dan prototypal inheritance, lalu menunjukkan bagaimana fitur ES6+ menyederhanakan pola yang sebelumnya kompleks. Tersedia banyak contoh praktis mengenai penggunaan arrow function, destructuring, template literal, optional chaining, dan nullish coalescing dalam kasus nyata.

Bagian kedua berfokus pada asynchronous programming. Anda akan memahami event loop secara visual, perbedaan microtask dan macrotask, serta menyusun alur async/await yang rapi. Studi kasus mencakup integrasi API eksternal, batching request paralel, dan error handling terpadu menggunakan helper utility.

Pada bab penutup, pembaca diajak membangun modul frontend kecil dengan bundler Vite. Materi mencakup tree-shaking, dynamic import, serta menulis unit test dengan Vitest. Dokumentasi juga menyertakan best practice struktur folder, linting dengan ESLint, dan strategi mempercepat build production.
TEXT,
                'price' => 85000,
            ],
            [
                'title' => 'Vue.js 3 Component Architecture',
                'content' => <<<'TEXT'
Panduan ini mengupas arsitektur komponen di Vue.js 3 dengan Composition API. Dimulai dengan membuat komponen dasar menggunakan `script setup`, memanfaatkan reactive state, dan melakukan data fetching secara deklaratif. Anda akan mempelajari bagaimana menyusun composable reusable, memisahkan logic presentational dan domain, serta menerapkan pattern slots kompleks untuk membuat layout dinamis.

Studi kasus utama membangun dashboard analitik lengkap dengan tabel, filter, dan chart interaktif. Materi menjelaskan penggunaan Pinia sebagai state management, integrasi dengan Vue Router terbaru, serta lazy-loading halaman untuk performa optimal. Disertai strategi mengelola permission dan route guard yang memanfaatkan meta field.

Bagian akhir membahas testing dan optimasi produksi. Anda akan menulis component test dengan Vue Test Utils, mengukur performa menggunakan Lighthouse, dan menerapkan code-splitting yang ramah SEO. Terdapat pula tips integrasi dengan TypeScript, auto-import, serta pengaturan konfigurasi build untuk aplikasi berskala besar.
TEXT,
                'price' => 120000,
            ],
            [
                'title' => 'DevOps CI/CD Pipeline',
                'content' => <<<'TEXT'
Dokumentasi ini membantu tim merancang pipeline CI/CD yang tangguh. Bab awal membedah konsep GitOps dan praktik branching strategy yang meminimalisir konflik. Kemudian pembaca diajak mengonfigurasi pipeline menggunakan GitHub Actions lengkap dengan matrix build, caching dependency, serta strategi failure notification via Slack.

Bagian berikutnya fokus pada penerapan containerization dengan Docker. Materi meliputi penulisan Dockerfile multi-stage, pengelolaan secret, serta orkestrasi deployment menggunakan Kubernetes dan Helm chart. Disediakan contoh environment staging dan production yang mendemonstrasikan rolling update, canary deployment, dan rollback otomatis.

Sebagai penutup, e-book membahas observabilitas pipeline. Anda akan mengintegrasikan Snyk untuk analisis keamanan, SonarQube untuk code quality, serta menyiapkan dashboard monitoring pipeline menggunakan Prometheus. Tersedia best practice penyusunan runbook dan checklist incident response agar tim siap menghadapi kegagalan produksi.
TEXT,
                'price' => 180000,
            ],
            [
                'title' => 'Database Normalization Guide',
                'content' => <<<'TEXT'
Catatan ini didesain sebagai referensi lengkap mengenai normalisasi database relasional. Bab pembuka menjelaskan latar belakang teoretis mulai dari 1NF hingga 5NF, lengkap dengan ilustrasi diagram yang mempermudah visualisasi relasi antar tabel. Setiap form normalisasi disertai contoh dataset sederhana yang diubah langkah demi langkah agar pembaca memahami konsekuensi tiap aturan.

Pada bagian praktik, Anda akan memodelkan modul inventaris dan penjualan yang umum ditemui di perusahaan ritel. Materi menunjukkan bagaimana menghindari redundansi, menjaga integritas referensial, serta menentukan primary key dan candidate key yang tepat. Disertai pula pembahasan mengenai denormalisasi terkontrol untuk kebutuhan analitik.

Penutup buku mengulas alat bantu modern seperti dbdiagram.io, Laravel Blueprint, dan migrasi berbasis kode. Dilengkapi panduan audit skema, checklist penamaan kolom, serta strategi validasi menggunakan constraint database agar struktur tetap konsisten sepanjang siklus pengembangan.
TEXT,
                'price' => 75000,
            ],
            [
                'title' => 'Laravel Testing Best Practices',
                'content' => <<<'TEXT'
Materi ini berfungsi sebagai kompas untuk membangun suite pengujian Laravel yang menyeluruh. Diawali dengan penjelasan perbedaan unit, feature, dan integration test beserta porsi idealnya. Anda akan mempelajari cara memanfaatkan Pest dan PHPUnit, menulis helper custom assertion, serta mengelola database testing menggunakan RefreshDatabase dan transaksi manual.

Contoh studi kasus mencakup pengujian modul pembayaran, notifikasi, serta interaksi dengan job queue dan event broadcasting. Terdapat juga pembahasan mengenai mocking dengan Mockery, penggunaan spy, dan cara menguji job yang berjalan di queue tanpa harus menjalankan worker secara penuh.

Bab akhir fokus pada otomatisasi. Pembaca akan menyiapkan pipeline CI yang menjalankan test paralel, mengukur code coverage, dan menghasilkan laporan lintasan regresi. Dilengkapi strategi mengelola test flaky, penjadwalan smoke test harian, serta tips membangun budaya quality-first di tim pengembang.
TEXT,
                'price' => 95000,
            ],
            [
                'title' => 'Security Best Practices',
                'content' => <<<'TEXT'
Dokumen keamanan ini menyatukan panduan best practice untuk melindungi aplikasi web modern. Bagian pertama mengulas OWASP Top 10 terbaru, cara mendeteksi dan mencegah injection, XSS, CSRF, hingga misconfiguration. Disertai checklist implementasi di Laravel, termasuk penggunaan middleware keamanan, validasi input ketat, dan sanitasi data pada setiap lapisan.

Bab kedua membahas keamanan autentikasi dan otorisasi. Anda akan belajar mengimplementasikan multi-factor authentication, passwordless login, dan rate limiting adaptif. Materi juga menyoroti pentingnya logging audit, pemantauan sesi aktif, serta strategi revocation token untuk API pribadi maupun publik.

Bagian akhir menyoroti aspek operasional: enkripsi data at rest dan in transit, pengelolaan secret menggunakan Vault atau AWS KMS, serta otomatisasi security scan dalam pipeline CI/CD. Panduan disertai langkah mitigasi insiden, template laporan keamanan, dan rencana komunikasi krisis agar tim siap menghadapi ancaman nyata.
TEXT,
                'price' => 140000,
            ],
        ];

        // Assign notes to sellers
        $tagList = [
            'Laravel',
            'PHP',
            'JavaScript',
            'Database',
            'Vue.js',
            'API',
            'Testing',
            'DevOps',
            'Security',
            'Tutorial',
            'Advanced',
            'Beginner',
            'Best Practices',
            'Design Patterns',
            'Web Development',
            'Backend',
            'Frontend'
        ];

        // Ensure tags exist
        foreach ($tagList as $tagName) {
            Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );
        }

        $allTags = Tag::all();

        // Get workspaces and folders for premium users
        $workspaces = Workspace::all();
        $folders = Folder::all();

        // Create notes for sellers
        foreach ($sellers as $index => $seller) {
            $notesPerSeller = rand(3, 6);

            // Check if seller has premium (has workspaces)
            $sellerWorkspaces = $workspaces->where('owner_id', $seller->id);
            $hasPremium = $sellerWorkspaces->isNotEmpty();

            for ($i = 0; $i < $notesPerSeller; $i++) {
                $templateIndex = ($index * $notesPerSeller + $i) % count($noteTemplates);
                $template = $noteTemplates[$templateIndex];

                // 30% chance to assign to workspace/folder if premium user
                $workspaceId = null;
                $folderId = null;

                if ($hasPremium && rand(1, 10) <= 3) {
                    $selectedWorkspace = $sellerWorkspaces->random();
                    $workspaceId = $selectedWorkspace->id;

                    // 50% chance to assign to folder
                    $workspaceFolders = $folders->where('workspace_id', $selectedWorkspace->id);
                    if ($workspaceFolders->isNotEmpty() && rand(1, 2) === 1) {
                        $folderId = $workspaceFolders->random()->id;
                    }
                }

                $content = $template['content'];
                $normalized = Str::of(strip_tags($content))
                    ->lower()
                    ->replaceMatches('/\s+/u', ' ')
                    ->trim();

                $note = Note::create([
                    'user_id' => $seller->id,
                    'original_creator_id' => $seller->id, // Set original creator
                    'workspace_id' => $workspaceId,
                    'folder_id' => $folderId,
                    'title' => $template['title'] . ($i > 0 ? ' ' . ($i + 1) : ''),
                    'content' => $content,
                    'content_hash' => hash('sha256', (string) $normalized),
                    'price' => $template['price'] + rand(-20000, 20000),
                    'is_public' => rand(0, 100) > 20, // 80% public
                    'status' => ['active', 'active', 'active', 'active', 'inactive'][rand(0, 4)],
                    'is_sold' => false, // New notes are not sold yet
                ]);

                // Attach random tags (2-4 tags per note)
                $randomTags = $allTags->random(rand(2, 4));
                $note->tags()->attach($randomTags);
            }
        }
    }
}
