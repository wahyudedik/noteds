<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    action: {
        type: String,
        default: 'request',
    },
    retryAfter: {
        type: Number,
        default: null,
    },
    message: {
        type: String,
        default: null,
    },
});

const actionMessages = {
    like: {
        title: 'Terlalu Banyak Like',
        description: 'Kamu terlalu banyak memberikan like dalam waktu singkat. Tunggu sebentar sebelum like lagi.',
        icon: 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5',
        emoji: '👍',
    },
    bookmark: {
        title: 'Terlalu Banyak Bookmark',
        description: 'Kamu terlalu banyak menambahkan bookmark dalam waktu singkat. Tunggu sebentar sebelum bookmark lagi.',
        icon: 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        emoji: '🔖',
    },
    comment: {
        title: 'Terlalu Banyak Komentar',
        description: 'Kamu terlalu banyak mengirim komentar dalam waktu singkat. Tunggu sebentar sebelum komentar lagi.',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    },
    post: {
        title: 'Terlalu Banyak Post',
        description: 'Kamu terlalu banyak membuat post dalam waktu singkat. Tunggu sebentar sebelum membuat post lagi.',
        icon: 'M12 4v16m8-8H4',
    },
    register: {
        title: 'Terlalu Banyak Percobaan Daftar',
        description: 'Terlalu banyak percobaan pendaftaran. Tunggu sebentar sebelum mencoba lagi.',
        icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    },
    login: {
        title: 'Terlalu Banyak Percobaan Login',
        description: 'Terlalu banyak percobaan login. Tunggu sebentar sebelum mencoba lagi.',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
    search: {
        title: 'Terlalu Banyak Pencarian',
        description: 'Kamu terlalu banyak melakukan pencarian dalam waktu singkat. Tunggu sebentar sebelum mencari lagi.',
        icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    },
    request: {
        title: 'Terlalu Banyak Request',
        description: 'Kamu terlalu banyak melakukan request dalam waktu singkat. Tunggu sebentar sebelum mencoba lagi.',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    },
};

const errorInfo = computed(() => {
    if (props.message) {
        return {
            title: 'Terlalu Banyak Request',
            description: props.message,
            icon: actionMessages.request.icon,
        };
    }
    return actionMessages[props.action] || actionMessages.request;
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

const goBack = () => {
    router.visit(route('home'));
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Header with gradient -->
                <div class="bg-gradient-to-r from-red-500 via-orange-500 to-yellow-500 p-6 text-center">
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
                            Error 429
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8">
                    <p class="text-gray-600 dark:text-gray-300 text-center mb-6 leading-relaxed">
                        {{ errorInfo.description }}
                    </p>

                    <div v-if="formatRetryAfter" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
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
                            @click="goBack"
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
                        >
                            Kembali ke Beranda
                        </button>
                        <button
                            @click="router.reload()"
                            class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-medium py-3 px-6 rounded-lg transition-all duration-200"
                        >
                            Muat Ulang Halaman
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 pb-6">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                            Rate limiting membantu menjaga performa dan keamanan platform
                        </p>
                    </div>
                </div>
            </div>

            <!-- Decorative elements -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Butuh bantuan? 
                    <a href="mailto:info@noteds.com" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        Email Support
                    </a>
                    <a href="https://wa.me/6281654932383" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        WhatsApp Support
                    </a>
                </p> 
            </div>
        </div>
    </div>
</template>

