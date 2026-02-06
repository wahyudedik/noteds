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
                        Kebijakan Pengembalian ini menjelaskan syarat dan ketentuan untuk pengembalian dana (refund) yang terkait dengan layanan Noteds.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">2. Status Pembayaran di Platform</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Saat ini Noteds tidak memproses pembayaran atau transaksi komersial di dalam platform. Karena itu, kebijakan refund pada umumnya tidak berlaku untuk aktivitas penggunaan fitur sosial/komunitas di Noteds.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Jika Anda mengalami penagihan yang tidak sesuai atau pembayaran pihak ketiga yang Anda yakini terkait layanan Noteds, silakan hubungi kami untuk investigasi.
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">3. Proses Pelaporan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Untuk melaporkan masalah penagihan atau permintaan klarifikasi, silakan hubungi kami melalui halaman <Link :href="route('legal.contact')" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Kontak</Link> dan sertakan informasi pendukung yang relevan (tanggal, nominal, bukti pembayaran, dan kronologi).
                    </p>
                </section>

                <section class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">4. Perubahan Kebijakan</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        Kami berhak mengubah Kebijakan Pengembalian ini kapan saja. Perubahan akan diberitahukan melalui platform dan berlaku efektif setelah dipublikasikan.
                    </p>
                </section>
            </div>
        </div>
    </Layout>
</template>

