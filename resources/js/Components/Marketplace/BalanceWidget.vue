<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const balance = computed(() => {
    return page.props.auth?.user?.balance || 0;
});
</script>

<template>
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
        <div class="text-sm opacity-90">Your Balance</div>
        <div class="text-2xl font-bold">
            Rp {{ new Intl.NumberFormat('id-ID').format(balance) }}
        </div>
    </div>
</template>

