<script setup>
import TooManyRequests from '@/Components/Errors/TooManyRequests.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
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
</script>

<template>
    <Head title="429 - Terlalu Banyak Request" />
    <TooManyRequests :action="action" :retry-after="retryAfter" :message="message" />
</template>

