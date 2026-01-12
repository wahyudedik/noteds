<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    supplier: {
        type: Object,
        required: true,
    },
});

const formatRating = (rating) => {
    return rating ? rating.toFixed(1) : '0.0';
};
</script>

<template>
    <div class="border rounded-lg p-4 hover:shadow-md transition bg-white dark:bg-gray-800">
        <div class="flex justify-between items-start mb-2">
            <h5 class="font-semibold text-gray-900 dark:text-gray-100">
                {{ supplier.name }}
            </h5>
            <div class="flex items-center gap-1">
                <span class="text-yellow-500">⭐</span>
                <span class="text-sm font-medium">{{ formatRating(supplier.rating) }}</span>
                <span class="text-xs text-gray-500">({{ supplier.review_count || 0 }})</span>
            </div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
            {{ supplier.description }}
        </p>
        <div v-if="supplier.specialties && supplier.specialties.length > 0" class="flex flex-wrap gap-2 mb-2">
            <span
                v-for="specialty in supplier.specialties"
                :key="specialty"
                class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded"
            >
                {{ specialty }}
            </span>
        </div>
        <div class="flex justify-between items-center mt-3">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ supplier.location || 'Lokasi tidak tersedia' }}
            </span>
            <Link
                :href="route('suppliers.show', supplier.id)"
                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
            >
                Lihat Detail →
            </Link>
        </div>
    </div>
</template>

