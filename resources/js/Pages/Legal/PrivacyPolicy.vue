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
    <Head title="Kebijakan Privasi - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Kebijakan Privasi</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

            <div class="prose prose-lg dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">1. Pengenalan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Noteds ("kami", "kita", atau "platform") menghormati privasi pengguna kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat menggunakan platform Noteds.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        Dengan menggunakan platform ini, Anda menyetujui pengumpulan dan penggunaan informasi sesuai dengan kebijakan ini.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Informasi yang Kami Kumpulkan</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.1 Informasi yang Anda Berikan</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Nama, alamat email, dan kata sandi saat registrasi</li>
                        <li>Informasi profil bisnis (nama bisnis, bidang usaha, skills, goals)</li>
                        <li>Konten yang Anda posting (posts, komentar, validasi ide)</li>
                        <li>Informasi transaksi (untuk Marketplace dan Clipper System)</li>
                        <li>Informasi pembayaran (melalui Midtrans, dienkripsi dan tidak disimpan oleh kami)</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.2 Informasi yang Dikumpulkan Otomatis</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Alamat IP dan informasi perangkat</li>
                        <li>Data penggunaan platform (halaman yang dikunjungi, waktu akses)</li>
                        <li>Cookies dan teknologi pelacakan serupa</li>
                        <li>Log sistem dan data teknis</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Cara Kami Menggunakan Informasi</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Kami menggunakan informasi yang dikumpulkan untuk:</p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Menyediakan dan meningkatkan layanan platform</li>
                        <li>Memproses transaksi dan pembayaran</li>
                        <li>Mengirim notifikasi penting terkait akun dan layanan</li>
                        <li>Menganalisis penggunaan platform untuk perbaikan</li>
                        <li>Mencegah penipuan dan penyalahgunaan</li>
                        <li>Mematuhi kewajiban hukum</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Pembagian Informasi</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Kami tidak menjual informasi pribadi Anda. Kami dapat membagikan informasi dalam situasi berikut:</p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>Penyedia Layanan:</strong> Dengan pihak ketiga yang membantu operasional platform (hosting, payment gateway, analytics)</li>
                        <li><strong>Kewajiban Hukum:</strong> Jika diwajibkan oleh hukum atau proses hukum</li>
                        <li><strong>Perlindungan Hak:</strong> Untuk melindungi hak, properti, atau keamanan kami dan pengguna lain</li>
                        <li><strong>Persetujuan:</strong> Dengan persetujuan eksplisit Anda</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">5. Keamanan Data</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang wajar untuk melindungi informasi pribadi Anda dari akses, pengungkapan, perubahan, atau penghancuran yang tidak sah.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        Namun, tidak ada metode transmisi melalui internet atau penyimpanan elektronik yang 100% aman. Kami tidak dapat menjamin keamanan absolut.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">6. Hak Anda</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Sesuai dengan UU Perlindungan Data Pribadi (UU PDP), Anda memiliki hak untuk:</p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Mengakses data pribadi Anda</li>
                        <li>Memperbaiki data yang tidak akurat</li>
                        <li>Menghapus data pribadi Anda</li>
                        <li>Menarik persetujuan penggunaan data</li>
                        <li>Mengajukan keberatan terhadap pemrosesan data</li>
                        <li>Mengunduh data Anda dalam format yang dapat dibaca</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300">
                        Untuk menggunakan hak-hak ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">7. Cookies</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami menggunakan cookies untuk meningkatkan pengalaman pengguna. Untuk informasi lebih lanjut, silakan lihat <Link :href="route('legal.cookie-policy')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kebijakan Cookie</Link> kami.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">8. Perubahan Kebijakan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan diberitahukan melalui platform atau email. Perubahan berlaku efektif setelah dipublikasikan.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">9. Kontak</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

