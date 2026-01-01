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
    <Head title="500 - Kesalahan Server" />
    <ErrorPage :status="500" :message="message" />
</template>

