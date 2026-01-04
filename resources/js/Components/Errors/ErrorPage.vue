<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
    title: {
        type: String,
        default: null,
    },
    message: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
    action: {
        type: String, 
        default: null,
    },
    retryAfter: {
        type: Number,
        default: null,
    },
});

const errorConfigs = {
    401: {
        title: 'Tidak Diizinkan',
        description: 'Kamu perlu login untuk mengakses halaman ini.',
        icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        gradient: 'from-blue-500 via-indigo-500 to-purple-500',
        buttonText: 'Masuk',
        buttonAction: () => router.visit(route('login')),
    },
    403: {
        title: 'Akses Ditolak',
        description: 'Kamu tidak memiliki izin untuk mengakses halaman ini.',
        icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
        gradient: 'from-orange-500 via-red-500 to-pink-500',
        buttonText: 'Kembali ke Beranda',
        buttonAction: () => router.visit(route('home')),
    },
    404: {
        title: 'Halaman Tidak Ditemukan',
        description: 'Halaman yang kamu cari tidak ditemukan atau sudah dihapus.',
        icon: 'M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        gradient: 'from-gray-500 via-gray-600 to-gray-700',
        buttonText: 'Kembali ke Beranda',
        buttonAction: () => router.visit(route('home')),
    },
    422: {
        title: 'Validasi Gagal',
        description: 'Data yang kamu kirim tidak valid. Silakan periksa kembali.',
        icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        gradient: 'from-yellow-500 via-orange-500 to-red-500',
        buttonText: 'Kembali',
        buttonAction: () => router.reload(),
    },
    429: {
        title: 'Terlalu Banyak Request',
        description: 'Kamu terlalu banyak melakukan request dalam waktu singkat. Tunggu sebentar sebelum mencoba lagi.',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        gradient: 'from-red-500 via-orange-500 to-yellow-500',
        buttonText: 'Kembali ke Beranda',
        buttonAction: () => router.visit(route('home')),
    },
    500: {
        title: 'Kesalahan Server',
        description: 'Terjadi kesalahan pada server. Tim kami telah diberitahu dan sedang memperbaikinya.',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        gradient: 'from-red-600 via-red-700 to-red-800',
        buttonText: 'Muat Ulang Halaman',
        buttonAction: () => router.reload(),
    },
    503: {
        title: 'Layanan Tidak Tersedia',
        description: 'Layanan sedang dalam pemeliharaan. Silakan coba lagi nanti.',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        gradient: 'from-purple-500 via-indigo-500 to-blue-500',
        buttonText: 'Muat Ulang Halaman',
        buttonAction: () => router.reload(),
    },
    413: {
        title: 'File Terlalu Besar',
        description: 'File yang kamu upload terlalu besar. Ukuran maksimal: 50MB.',
        icon: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
        gradient: 'from-pink-500 via-rose-500 to-red-500',
        buttonText: 'Kembali',
        buttonAction: () => router.reload(),
    },
};

const errorInfo = computed(() => {
    const config = errorConfigs[props.status] || {
        title: props.title || 'Terjadi Kesalahan',
        description: props.description || props.message || 'Terjadi kesalahan yang tidak terduga.',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        gradient: 'from-gray-500 via-gray-600 to-gray-700',
        buttonText: 'Kembali ke Beranda',
        buttonAction: () => router.visit(route('home')),
    };

    return {
        title: props.title || config.title,
        description: props.description || props.message || config.description,
        icon: config.icon,
        gradient: config.gradient,
        buttonText: config.buttonText,
        buttonAction: config.buttonAction,
    };
});

const formatRetryAfter = computed(() => {
    if (!props.retryAfter) return null;
    
    if (props.retryAfter < 60) {
        return `${props.retryAfter} detik`;
    } else if (props.retryAfter < 3600) {
        const minutes = Math.ceil(props.retryAfter / 60);
        return `${minutes} menit`;
    } else {
        const hours = Math.ceil(props.retryAfter / 3600);
        return `${hours} jam`;
    }
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Header with gradient -->
                <div :class="['bg-gradient-to-r', errorInfo.gradient, 'p-6 text-center']">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full backdrop-blur-sm mb-4">
                        <svg
                            class="w-10 h-10 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="errorInfo.icon"
                            />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">
                        {{ errorInfo.title }}
                    </h1>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-white">
                            Error {{ status }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <p class="text-gray-600 dark:text-gray-300 text-center mb-6 leading-relaxed">
                        {{ errorInfo.description }}
                    </p>

                    <div v-if="formatRetryAfter && status === 429" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                                    Coba lagi dalam
                                </p>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    {{ formatRetryAfter }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button
                            @click="errorInfo.buttonAction"
                            :class="[
                                'w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl'
                            ]"
                        >
                            {{ errorInfo.buttonText }}
                        </button>
                        <button
                            v-if="status !== 401"
                            @click="router.reload()"
                            class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-medium py-3 px-6 rounded-lg transition-all duration-200"
                        >
                            Muat Ulang Halaman
                        </button>
                        <button
                            v-if="status === 401"
                            @click="router.visit(route('register'))"
                            class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-medium py-3 px-6 rounded-lg transition-all duration-200"
                        >
                            Daftar Akun Baru
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 pb-6">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                            <span v-if="status === 429">Rate limiting membantu menjaga performa dan keamanan platform</span>
                            <span v-else-if="status === 500">Jika masalah berlanjut, silakan hubungi support</span>
                            <span v-else-if="status === 503">Kami sedang melakukan pemeliharaan untuk pengalaman yang lebih baik</span>
                            <span v-else>Jika masalah berlanjut, silakan hubungi support</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Decorative elements -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Butuh bantuan? 
                    <a href="mailto:info@noteds.com" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        Email Support | 
                    </a>
                    <a href="https://wa.me/6281654932383" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        WhatsApp Support
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

