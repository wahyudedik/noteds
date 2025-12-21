<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    productId: {
        type: Number,
        required: true,
    },
});

const download = () => {
    window.location.href = route('marketplace.products.download', productId);
};
</script>

<template>
    <button
        @click="download"
        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
    >
        Download
    </button>
</template>

