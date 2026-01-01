<script setup>
import ErrorPage from '@/Components/Errors/ErrorPage.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <Head title="419 - Sesi Berakhir" />
    <ErrorPage :status="419" :message="message || 'Sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.'" />
</template>

