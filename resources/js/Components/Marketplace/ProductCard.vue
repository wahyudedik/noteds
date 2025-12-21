<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="route('marketplace.products.show', product.id)"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
    >
        <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-full h-48 object-cover"
            />
            <div v-else class="w-full h-48 flex items-center justify-center text-gray-400">
                No Image
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                {{ product.name }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                {{ product.description }}
            </p>
            <div class="flex justify-between items-center">
                <span class="text-xl font-bold text-blue-600">
                    Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ product.sales_count || 0 }} sales
                </span>
            </div>
            <div v-if="product.category" class="mt-2">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                    {{ product.category }}
                </span>
            </div>
        </div>
    </Link>
</template>

