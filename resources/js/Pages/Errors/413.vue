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
    <Head title="413 - File Terlalu Besar" />
    <ErrorPage :status="413" :message="message" />
</template>

