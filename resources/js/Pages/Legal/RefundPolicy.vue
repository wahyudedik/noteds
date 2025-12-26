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
    <Head title="Kebijakan Pengembalian - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Kebijakan Pengembalian</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Terakhir diperbarui: {{ new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>

            <div class="prose prose-lg dark:prose-invert max-w-none">
                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">1. Pengenalan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kebijakan Pengembalian ini menjelaskan syarat dan ketentuan untuk pengembalian dana (refund) di platform Noteds, khususnya untuk transaksi di <strong>Marketplace Digital</strong> dan <strong>Clipper System</strong>.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Marketplace Digital</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.1 Produk Digital</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Karena sifat produk digital yang dapat diunduh dan digunakan segera setelah pembelian, <strong>pengembalian dana untuk produk digital umumnya tidak tersedia</strong> setelah produk diunduh atau license key diberikan.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Namun, pengembalian dana dapat dipertimbangkan dalam kasus berikut:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Produk tidak sesuai dengan deskripsi yang dijanjikan</li>
                        <li>Produk rusak atau tidak dapat digunakan</li>
                        <li>Produk tidak dapat diunduh setelah pembayaran</li>
                        <li>Kesalahan teknis dari pihak platform</li>
                        <li>Pembelian duplikat yang tidak disengaja (dalam 24 jam)</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.2 Proses Pengembalian</h3>
                    <ol class="list-decimal pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Ajukan permintaan refund melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link> atau melalui order detail</li>
                        <li>Sertakan bukti pembelian dan alasan refund</li>
                        <li>Tim kami akan meninjau permintaan dalam 3-5 hari kerja</li>
                        <li>Jika disetujui, dana akan dikembalikan ke metode pembayaran asli dalam 5-10 hari kerja</li>
                    </ol>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">2.3 Waktu Pengajuan</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Permintaan refund harus diajukan <strong>maksimal 7 hari</strong> setelah tanggal pembelian. Setelah periode ini, permintaan refund tidak akan dipertimbangkan kecuali ada alasan khusus yang disetujui oleh admin.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Clipper System</h2>
                    
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3.1 Untuk Brand/Influencer</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <strong>Budget yang sudah di-lock untuk campaign tidak dapat dikembalikan</strong> setelah campaign diaktifkan, kecuali:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Campaign dibatalkan sebelum ada clip yang di-submit</li>
                        <li>Campaign dibatalkan karena alasan teknis dari platform</li>
                        <li>Campaign dibatalkan karena pelanggaran dari Clipper (dengan persetujuan admin)</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <strong>Sisa budget</strong> yang tidak terpakai akan dikembalikan otomatis ke Creator Wallet setelah campaign selesai.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">3.2 Untuk Clipper</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <strong>Reward yang sudah diterima tidak dapat dikembalikan</strong> setelah ditransfer ke Clipper Wallet, kecuali:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Terjadi kesalahan perhitungan reward (dengan bukti yang jelas)</li>
                        <li>Clip terbukti melanggar hak cipta atau aturan campaign</li>
                        <li>Terjadi fraud atau manipulasi views yang terbukti</li>
                    </ul>
                    <p class="text-gray-700 dark:text-gray-300">
                        Dalam kasus ini, admin berhak untuk meninjau dan memutuskan pengembalian reward.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Top Up Saldo</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <strong>Top up saldo tidak dapat dikembalikan</strong> setelah berhasil diproses, karena saldo dapat langsung digunakan untuk transaksi di platform.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300">
                        Jika terjadi kesalahan top up (misalnya jumlah yang salah), silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link> untuk bantuan.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">5. Metode Pengembalian</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Pengembalian dana akan dilakukan melalui metode pembayaran asli yang digunakan:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li><strong>Kartu Kredit/Debit:</strong> Dikembalikan ke kartu yang sama (5-10 hari kerja)</li>
                        <li><strong>E-Wallet (OVO, GoPay, DANA, dll):</strong> Dikembalikan ke e-wallet yang sama (1-3 hari kerja)</li>
                        <li><strong>Bank Transfer:</strong> Dikembalikan ke rekening bank (3-5 hari kerja)</li>
                        <li><strong>Saldo Platform:</strong> Dikembalikan ke saldo akun (langsung)</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">6. Biaya Pengembalian</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        <strong>Kami tidak mengenakan biaya untuk pengembalian dana</strong> yang disetujui. Namun, biaya transfer dari payment gateway (jika ada) akan ditanggung oleh kami.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">7. Sengketa</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika terjadi sengketa terkait refund yang tidak dapat diselesaikan, kami akan:
                    </p>
                    <ol class="list-decimal pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Meninjau semua bukti dan informasi yang relevan</li>
                        <li>Berusaha mencapai kesepakatan yang adil untuk semua pihak</li>
                        <li>Jika diperlukan, melibatkan mediator pihak ketiga</li>
                    </ol>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">8. Pengecualian</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Pengembalian dana <strong>tidak akan diberikan</strong> untuk:
                    </p>
                    <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4 space-y-2">
                        <li>Perubahan pikiran setelah produk diunduh atau digunakan</li>
                        <li>Ketidakpuasan dengan kualitas produk (kecuali tidak sesuai deskripsi)</li>
                        <li>Permintaan refund setelah melewati batas waktu yang ditentukan</li>
                        <li>Akun yang melanggar Syarat & Ketentuan</li>
                        <li>Transaksi yang dicurigai sebagai penipuan</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">9. Perubahan Kebijakan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berhak mengubah Kebijakan Pengembalian ini kapan saja. Perubahan akan diberitahukan melalui platform. Transaksi yang sudah dilakukan sebelum perubahan akan tetap mengikuti kebijakan yang berlaku saat transaksi.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">10. Kontak</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Untuk mengajukan permintaan refund atau pertanyaan tentang Kebijakan Pengembalian ini, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link>.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

