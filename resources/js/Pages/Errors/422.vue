<script setup>
import ErrorPage from '@/Components/Errors/ErrorPage.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="422 - Validasi Gagal" />
    <ErrorPage :status="422" :message="message" />
</template>

