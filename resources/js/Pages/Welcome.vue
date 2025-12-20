<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome to Noteds - Social Network for Business Thinkers & Doers" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-2">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="text-xl font-bold text-gray-900 dark:text-gray-100">Noteds</span>
                    </Link>

                    <div class="flex items-center gap-4">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl md:text-6xl dark:text-white">
                    <span class="block">Social Network for</span>
                    <span class="block text-indigo-600 dark:text-indigo-400">Business Thinkers & Doers</span>
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-600 dark:text-gray-300">
                    Platform untuk berbagi ide bisnis, validasi ide, diskusi serius, dan kolaborasi. 
                    Bukan tempat galau, tapi tempat membangun bisnis.
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg bg-indigo-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                    >
                        Mulai Gratis
                    </Link>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-lg bg-white px-8 py-3 text-base font-semibold text-gray-900 shadow-lg ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700 transition"
                    >
                        Masuk
                    </Link>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mt-24">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Purpose-Based Posts</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Setiap post punya tujuan jelas: ide bisnis, validasi, diskusi masalah, atau cari partner. Bukan status galau.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Thread Diskusi Berkualitas</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Diskusi terstruktur dengan voting, best answer, dan thread yang fokus pada solusi bisnis nyata.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Idea Validation</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Validasi ide bisnis dari komunitas berpengalaman. Dapat feedback, estimasi modal, dan analisis risiko.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Business Profile</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Profile bisnis lengkap: bidang usaha, skills, goals, dan portofolio. Bukan sekadar foto profil.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Mentor & Partner</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Temukan mentor, partner bisnis, atau kolaborator yang tepat untuk mengembangkan ide Anda.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-gray-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Moderasi Ketat</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Komunitas berkualitas dengan aturan jelas: tidak ada curhat, drama, atau hoaks. Fokus bisnis saja.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-24 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-16 text-center">
                <h2 class="text-3xl font-bold text-white">Siap Membangun Bisnis Anda?</h2>
                <p class="mt-4 text-lg text-indigo-100">
                    Bergabung dengan komunitas pebisnis dan pengusaha yang serius
                </p>
                <div class="mt-8">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex rounded-lg bg-white px-8 py-3 text-base font-semibold text-indigo-600 shadow-lg hover:bg-gray-50 transition"
                    >
                        Daftar Sekarang - Gratis
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} Noteds. Social Network for Business Thinkers & Doers.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
