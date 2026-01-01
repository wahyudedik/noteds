<script setup>
import ErrorPage from '@/Components/Errors/ErrorPage.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    },
    bookmark: {
        title: 'Terlalu Banyak Bookmark',
        description: 'Kamu terlalu banyak menambahkan bookmark dalam waktu singkat. Tunggu sebentar sebelum bookmark lagi.',
    },
    comment: {
        title: 'Terlalu Banyak Komentar',
        description: 'Kamu terlalu banyak mengirim komentar dalam waktu singkat. Tunggu sebentar sebelum komentar lagi.',
    },
    post: {
        title: 'Terlalu Banyak Post',
        description: 'Kamu terlalu banyak membuat post dalam waktu singkat. Tunggu sebentar sebelum membuat post lagi.',
    },
    register: {
        title: 'Terlalu Banyak Percobaan Daftar',
        description: 'Terlalu banyak percobaan pendaftaran. Tunggu sebentar sebelum mencoba lagi.',
    },
    login: {
        title: 'Terlalu Banyak Percobaan Login',
        description: 'Terlalu banyak percobaan login. Tunggu sebentar sebelum mencoba lagi.',
    },
    search: {
        title: 'Terlalu Banyak Pencarian',
        description: 'Kamu terlalu banyak melakukan pencarian dalam waktu singkat. Tunggu sebentar sebelum mencari lagi.',
    },
    request: {
        title: 'Terlalu Banyak Request',
        description: 'Kamu terlalu banyak melakukan request dalam waktu singkat. Tunggu sebentar sebelum mencoba lagi.',
    },
};

const errorInfo = computed(() => {
    if (props.message) {
        return {
            title: 'Terlalu Banyak Request',
            description: props.message,
        };
    }
    return actionMessages[props.action] || actionMessages.request;
});
</script>

<template>
    <Head title="429 - Terlalu Banyak Request" />
    <ErrorPage 
        :status="429" 
        :retry-after="retryAfter" 
        :title="errorInfo.title"
        :description="errorInfo.description"
    />
</template>
