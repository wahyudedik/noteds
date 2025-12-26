<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;
</script>

<template>
    <Head title="Disclaimer - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Disclaimer</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

            <div class="prose prose-lg dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">1. Informasi Umum</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Informasi yang tersedia di platform Noteds disediakan untuk tujuan informasi umum saja. Meskipun kami berusaha untuk menjaga informasi tetap terkini dan akurat, kami tidak membuat pernyataan atau jaminan apapun, tersurat maupun tersirat, mengenai kelengkapan, keakuratan, keandalan, kesesuaian, atau ketersediaan informasi, produk, layanan, atau konten terkait yang terdapat di platform.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Bukan Nasihat Profesional</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.1 Bukan Nasihat Hukum</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Konten di platform ini tidak dimaksudkan sebagai nasihat hukum. Informasi yang disediakan tidak menggantikan konsultasi dengan pengacara atau penasihat hukum yang berkualifikasi.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.2 Bukan Nasihat Keuangan</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Informasi terkait bisnis, investasi, atau keuangan yang terdapat di platform ini bukan merupakan nasihat keuangan profesional. Kami tidak bertanggung jawab atas keputusan keuangan yang Anda buat berdasarkan informasi di platform ini.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.3 Bukan Nasihat Bisnis</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Meskipun platform ini menyediakan forum diskusi bisnis dan validasi ide, informasi yang dibagikan oleh pengguna lain bukan merupakan nasihat bisnis profesional. Setiap keputusan bisnis adalah tanggung jawab Anda sendiri.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Konten Pengguna</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Platform Noteds memungkinkan pengguna untuk memposting konten, berbagi ide, dan berpartisipasi dalam diskusi. Kami tidak bertanggung jawab atas:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Akurasi, kelengkapan, atau keandalan konten yang diposting oleh pengguna</li>
                        <li>Keputusan yang Anda buat berdasarkan konten pengguna lain</li>
                        <li>Kerugian yang timbul dari penggunaan informasi yang dibagikan oleh pengguna lain</li>
                        <li>Validasi ide atau saran yang diberikan oleh pengguna lain</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Marketplace & Transaksi</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Noteds menyediakan platform untuk transaksi di Marketplace Digital dan Clipper System. Kami bertindak sebagai perantara dan tidak bertanggung jawab atas:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Kualitas, keamanan, atau legalitas produk yang dijual di Marketplace</li>
                        <li>Konten yang di-submit oleh Clipper dalam Clipper System</li>
                        <li>Sengketa antara penjual dan pembeli</li>
                        <li>Kerugian finansial akibat transaksi antara pengguna</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300">
                        Semua transaksi adalah tanggung jawab pihak yang terlibat. Kami menyediakan sistem escrow untuk keamanan, namun tidak menjamin hasil transaksi.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">5. Link Eksternal</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Platform ini mungkin berisi link ke website eksternal. Kami tidak memiliki kendali atas konten, kebijakan privasi, atau praktik website pihak ketiga dan tidak bertanggung jawab atas konten atau layanan mereka.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">6. Ketersediaan Layanan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berusaha untuk menjaga platform tetap tersedia, namun kami tidak menjamin bahwa platform akan selalu dapat diakses tanpa gangguan. Platform mungkin mengalami downtime untuk pemeliharaan, perbaikan, atau alasan teknis lainnya.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">7. Batasan Tanggung Jawab</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Dalam batas maksimum yang diizinkan oleh hukum, Noteds dan afiliasinya tidak bertanggung jawab atas:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Kerugian langsung, tidak langsung, insidental, atau konsekuensial</li>
                        <li>Kehilangan keuntungan, data, atau peluang bisnis</li>
                        <li>Kerusakan yang timbul dari penggunaan atau ketidakmampuan menggunakan platform</li>
                        <li>Keputusan yang dibuat berdasarkan informasi di platform</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">8. Penggunaan Atas Risiko Sendiri</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Penggunaan platform Noteds sepenuhnya merupakan risiko Anda sendiri. Anda bertanggung jawab untuk mengevaluasi keakuratan, kelengkapan, dan kegunaan informasi yang tersedia di platform.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">9. Perubahan Disclaimer</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berhak mengubah Disclaimer ini kapan saja tanpa pemberitahuan sebelumnya. Perubahan akan berlaku efektif setelah dipublikasikan di platform.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">10. Kontak</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda memiliki pertanyaan tentang Disclaimer ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

