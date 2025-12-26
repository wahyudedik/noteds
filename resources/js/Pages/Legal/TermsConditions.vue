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
    <Head title="Syarat & Ketentuan - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Syarat & Ketentuan</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

            <div class="prose prose-lg dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">1. Penerimaan Syarat</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Dengan mengakses dan menggunakan platform Noteds, Anda menyetujui untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak setuju dengan syarat-syarat ini, mohon untuk tidak menggunakan platform ini.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Definisi</h2>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>"Platform"</strong> mengacu pada website Noteds dan semua layanannya</li>
                        <li><strong>"Pengguna"</strong> adalah individu yang mengakses atau menggunakan platform</li>
                        <li><strong>"Konten"</strong> adalah semua informasi, data, teks, gambar, atau materi lainnya yang diposting di platform</li>
                        <li><strong>"Layanan"</strong> mencakup Forum, Marketplace Digital, dan Clipper System</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Akun Pengguna</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3.1 Registrasi</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Anda harus berusia minimal 18 tahun atau memiliki persetujuan orang tua</li>
                        <li>Anda bertanggung jawab untuk menjaga kerahasiaan akun dan kata sandi</li>
                        <li>Anda harus memberikan informasi yang akurat dan terkini</li>
                        <li>Satu orang hanya boleh memiliki satu akun</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3.2 Penutupan Akun</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berhak menutup atau menangguhkan akun Anda jika Anda melanggar Syarat & Ketentuan ini, melakukan aktivitas penipuan, atau menyalahgunakan platform.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Aturan Penggunaan</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4.1 Larangan</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">Anda dilarang untuk:</p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Mem posting konten ilegal, menyesatkan, atau melanggar hak cipta</li>
                        <li>Melakukan spam, phishing, atau aktivitas penipuan</li>
                        <li>Mengganggu atau merusak operasional platform</li>
                        <li>Menggunakan bot atau skrip otomatis tanpa izin</li>
                        <li>Menyebarkan virus, malware, atau kode berbahaya</li>
                        <li>Melakukan peretasan atau mencoba mengakses data pengguna lain</li>
                        <li>Mem posting konten yang mengandung ujaran kebencian, diskriminasi, atau pornografi</li>
                        <li>Menyalahgunakan fitur Marketplace atau Clipper System untuk tujuan ilegal</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">4.2 Konten yang Diizinkan</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Platform ini fokus pada diskusi bisnis, entrepreneurship, dan kolaborasi profesional. Konten harus relevan dengan tujuan tersebut.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">5. Hak Kekayaan Intelektual</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5.1 Konten Platform</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Semua konten di platform (logo, desain, teks, kode) adalah milik Noteds atau pemberi lisensi dan dilindungi oleh hukum hak cipta.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">5.2 Konten Pengguna</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Dengan memposting konten di platform, Anda memberikan lisensi non-eksklusif kepada Noteds untuk menggunakan, menampilkan, dan mendistribusikan konten tersebut di platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        Anda tetap mempertahankan kepemilikan konten Anda dan bertanggung jawab untuk memastikan Anda memiliki hak untuk memposting konten tersebut.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">6. Marketplace Digital</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">6.1 Penjual</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Penjual bertanggung jawab atas keakuratan deskripsi produk</li>
                        <li>Produk digital harus legal dan tidak melanggar hak cipta</li>
                        <li>Penjual harus memproses pesanan sesuai dengan yang dijanjikan</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">6.2 Pembeli</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Pembeli bertanggung jawab untuk memverifikasi produk sebelum membeli</li>
                        <li>Pembayaran dilakukan melalui Midtrans dan mengikuti kebijakan mereka</li>
                        <li>Refund mengikuti <Link :href="route('legal.refund-policy')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kebijakan Pengembalian</Link> kami</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">7. Clipper System</h2>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">7.1 Brand/Influencer</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Brand bertanggung jawab untuk menyediakan budget yang cukup untuk campaign</li>
                        <li>Campaign harus mematuhi hukum dan regulasi yang berlaku</li>
                        <li>Budget yang sudah di-lock tidak dapat dikembalikan kecuali campaign dibatalkan</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">7.2 Clipper</h3>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Clipper bertanggung jawab untuk submit konten yang original dan sesuai dengan campaign</li>
                        <li>Konten yang di-submit tidak boleh melanggar hak cipta atau hak pihak ketiga</li>
                        <li>Reward akan dibayarkan setelah validasi views dan approval</li>
                        <li>Clipper tidak dapat menarik reward yang masih dalam status pending</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">8. Batasan Tanggung Jawab</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Noteds menyediakan platform "sebagaimana adanya". Kami tidak menjamin bahwa platform akan selalu tersedia, bebas dari kesalahan, atau aman sepenuhnya.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami tidak bertanggung jawab atas:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Kerugian langsung atau tidak langsung akibat penggunaan platform</li>
                        <li>Konten yang diposting oleh pengguna lain</li>
                        <li>Transaksi antara pengguna di Marketplace atau Clipper System</li>
                        <li>Kehilangan data atau gangguan layanan</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">9. Ganti Rugi</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Anda setuju untuk mengganti kerugian dan membebaskan Noteds dari semua klaim, kerugian, kerusakan, kewajiban, dan biaya (termasuk biaya hukum) yang timbul dari pelanggaran Anda terhadap Syarat & Ketentuan ini.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">10. Hukum yang Berlaku</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Syarat & Ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap sengketa akan diselesaikan melalui pengadilan yang berwenang di Indonesia.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">11. Perubahan Syarat</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berhak mengubah Syarat & Ketentuan ini kapan saja. Perubahan akan diberitahukan melalui platform. Penggunaan berkelanjutan setelah perubahan berarti Anda menerima syarat yang baru.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">12. Kontak</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda memiliki pertanyaan tentang Syarat & Ketentuan ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

