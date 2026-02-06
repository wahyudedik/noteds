<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('legal.contact.submit'), {
        onSuccess: () => {
            form.reset();
            alert('Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
        },
    });
};
</script>

<template>
    <Head title="Kontak - Noteds" />
    
    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Hubungi Kami</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                Ada pertanyaan, saran, atau butuh bantuan? Kami siap membantu Anda.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <!-- Contact Information -->
                <div class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Informasi Kontak</h2>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Email</p>
                                    <a href="mailto:support@noteds.com" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">support@noteds.com</a>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Alamat</p>
                                    <p class="text-gray-700 dark:text-gray-300">Indonesia</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Waktu Respon</p>
                                    <p class="text-gray-700 dark:text-gray-300">Senin - Jumat: 09:00 - 17:00 WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Butuh Bantuan Cepat?</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                            Untuk pertanyaan umum, silakan cek FAQ atau dokumentasi kami terlebih dahulu.
                        </p>
                        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                            <li>• Pertanyaan tentang akun: support@noteds.com</li>
                            <li>• Masalah teknis: tech@noteds.com</li>
                            <li>• Pertanyaan bisnis: business@noteds.com</li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Kirim Pesan</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nama <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Subjek <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="subject"
                                v-model="form.subject"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.subject }"
                            >
                                <option value="">Pilih subjek</option>
                                <option value="general">Pertanyaan Umum</option>
                                <option value="account">Masalah Akun</option>
                                <option value="technical">Masalah Teknis</option>
                                <option value="business">Pertanyaan Bisnis</option>
                                <option value="legal">Pertanyaan Legal</option>
                                <option value="other">Lainnya</option>
                            </select>
                            <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Pesan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                required
                                rows="5"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.message }"
                            ></textarea>
                            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span v-if="form.processing">Mengirim...</span>
                            <span v-else>Kirim Pesan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>

