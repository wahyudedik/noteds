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
    <Head title="403 - Akses Ditolak" />
    <ErrorPage :status="403" :message="message" />
</template>

