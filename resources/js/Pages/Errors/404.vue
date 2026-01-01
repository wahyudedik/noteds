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
    <Head title="404 - Halaman Tidak Ditemukan" />
    <ErrorPage :status="404" :message="message" />
</template>

