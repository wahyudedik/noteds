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
    <Head title="503 - Layanan Tidak Tersedia" />
    <ErrorPage :status="503" :message="message" />
</template>

