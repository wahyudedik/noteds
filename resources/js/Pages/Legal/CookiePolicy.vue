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
    <Head title="Kebijakan Cookie - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Kebijakan Cookie</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

            <div class="prose prose-lg dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">1. Apa itu Cookie?</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie adalah file teks kecil yang disimpan di perangkat Anda (komputer, tablet, atau ponsel) saat Anda mengunjungi website. Cookie membantu website mengingat informasi tentang kunjungan Anda, seperti preferensi bahasa dan pengaturan lainnya, yang dapat membuat kunjungan berikutnya lebih mudah dan situs lebih berguna bagi Anda.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Jenis Cookie yang Kami Gunakan</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.1 Cookie Esensial</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie ini diperlukan untuk pengoperasian platform. Mereka memungkinkan Anda untuk menavigasi platform dan menggunakan fitur-fitur dasarnya, seperti mengakses area yang aman dan mengingat preferensi login Anda.
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>Cookie Sesi:</strong> Menyimpan informasi saat Anda menggunakan platform</li>
                        <li><strong>Cookie Autentikasi:</strong> Mengingat status login Anda</li>
                        <li><strong>Cookie Keamanan:</strong> Melindungi dari serangan keamanan</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.2 Cookie Fungsional</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie ini memungkinkan platform untuk mengingat pilihan yang Anda buat (seperti nama pengguna, bahasa, atau wilayah) dan menyediakan fitur yang lebih personal.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.3 Cookie Analitik</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie ini membantu kami memahami bagaimana pengunjung berinteraksi dengan platform dengan mengumpulkan dan melaporkan informasi secara anonim. Kami menggunakan layanan seperti Google Analytics untuk tujuan ini.
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Jumlah pengunjung</li>
                        <li>Halaman yang paling sering dikunjungi</li>
                        <li>Waktu yang dihabiskan di platform</li>
                        <li>Sumber lalu lintas</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.4 Cookie Pemasaran (jika ada)</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika kami menggunakan iklan atau pemasaran pihak ketiga, cookie ini dapat digunakan untuk menampilkan iklan yang relevan dengan minat Anda.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Cookie Pihak Ketiga</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Beberapa cookie di platform kami disetel oleh layanan pihak ketiga. Cookie ini dapat digunakan untuk:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>Google Analytics:</strong> Untuk menganalisis penggunaan platform</li>
                        <li><strong>Midtrans:</strong> Untuk memproses pembayaran (jika menggunakan fitur Marketplace atau Clipper)</li>
                        <li><strong>Layanan Autentikasi:</strong> Untuk keamanan login</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300">
                        Kami tidak memiliki kendali atas cookie pihak ketiga. Silakan merujuk pada kebijakan privasi mereka untuk informasi lebih lanjut.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Durasi Cookie</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4.1 Cookie Sesi</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie ini bersifat sementara dan akan dihapus saat Anda menutup browser.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4.2 Cookie Persisten</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cookie ini tetap tersimpan di perangkat Anda untuk periode waktu tertentu atau sampai Anda menghapusnya secara manual. Durasi bervariasi tergantung pada jenis cookie.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">5. Mengelola Cookie</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5.1 Melalui Browser</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Sebagian besar browser secara otomatis menerima cookie, tetapi Anda biasanya dapat memodifikasi pengaturan browser untuk menolak cookie jika Anda lebih suka. Namun, ini dapat mencegah Anda mengambil keuntungan penuh dari platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Cara mengelola cookie di browser populer:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>Chrome:</strong> Settings > Privacy and security > Cookies and other site data</li>
                        <li><strong>Firefox:</strong> Options > Privacy & Security > Cookies and Site Data</li>
                        <li><strong>Safari:</strong> Preferences > Privacy > Cookies and website data</li>
                        <li><strong>Edge:</strong> Settings > Cookies and site permissions</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5.2 Melalui Platform</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami akan menyediakan opsi untuk mengelola preferensi cookie di platform (jika tersedia). Anda dapat mengakses pengaturan ini melalui profil akun Anda.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">6. Dampak Menolak Cookie</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda memilih untuk menolak cookie, beberapa fitur platform mungkin tidak berfungsi dengan baik atau tidak dapat diakses. Ini termasuk:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Fitur login dan autentikasi</li>
                        <li>Preferensi pengguna yang disimpan</li>
                        <li>Fitur Marketplace dan Clipper System</li>
                        <li>Personalisasi konten</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">7. Cookie dan Data Pribadi</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Beberapa cookie yang kami gunakan dapat mengumpulkan informasi pribadi. Untuk informasi lebih lanjut tentang bagaimana kami menggunakan data pribadi, silakan lihat <Link :href="route('legal.privacy-policy')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kebijakan Privasi</Link> kami.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">8. Perubahan Kebijakan Cookie</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami dapat memperbarui Kebijakan Cookie ini dari waktu ke waktu untuk mencerminkan perubahan dalam praktik kami atau karena alasan operasional, hukum, atau peraturan lainnya. Perubahan akan diberitahukan melalui platform.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">9. Kontak</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda memiliki pertanyaan tentang Kebijakan Cookie ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

