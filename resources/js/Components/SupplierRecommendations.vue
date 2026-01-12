<script setup>
import SupplierCard from './SupplierCard.vue';

const props = defineProps({
    recommendations: {
        type: Array,
        default: () => [],
    },
    businessType: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div v-if="recommendations && recommendations.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
            🛒 Rekomendasi Supplier untuk Bisnis Anda
        </h3>

        <div v-for="categoryGroup in recommendations" :key="categoryGroup.category" class="mb-6 last:mb-0">
            <div class="flex items-center gap-2 mb-3">
                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                    {{ categoryGroup.category_label }}
                </h4>
                <span v-if="categoryGroup.note" class="text-sm text-gray-500 dark:text-gray-400">
                    ({{ categoryGroup.note }})
                </span>
            </div>

            <div v-if="categoryGroup.suppliers && categoryGroup.suppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <SupplierCard
                    v-for="supplier in categoryGroup.suppliers"
                    :key="supplier.id"
                    :supplier="supplier"
                />
            </div>
            <p v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                Belum ada supplier tersedia untuk kategori ini.
            </p>
        </div>
    </div>
</template>

