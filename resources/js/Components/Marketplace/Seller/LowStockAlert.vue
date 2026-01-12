<script setup>
import { Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    threshold: {
        type: Number,
        default: 10,
    },
});

const goToInventory = () => {
    router.visit(route('marketplace.seller.inventory.index'));
};
</script>

<template>
    <div
        v-if="products && products.length > 0"
        class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <svg
                        class="w-5 h-5 text-yellow-600 dark:text-yellow-400"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">
                        Low Stock Alert
                    </h3>
                </div>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                    {{ products.length }} product{{ products.length > 1 ? 's' : '' }} {{ products.length > 1 ? 'are' : 'is' }} running low on stock (below {{ threshold }} units)
                </p>
                <div class="space-y-2">
                    <div
                        v-for="product in products.slice(0, 5)"
                        :key="product.id"
                        class="flex items-center justify-between bg-white dark:bg-gray-800 rounded p-2"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ product.name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Stock: {{ product.stock || 0 }} units
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="
                                    (product.stock || 0) <= threshold / 2
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                "
                            >
                                {{ product.stock || 0 }} left
                            </span>
                        </div>
                    </div>
                    <p v-if="products.length > 5" class="text-sm text-yellow-700 dark:text-yellow-300 mt-2">
                        ... and {{ products.length - 5 }} more
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <PrimaryButton @click="goToInventory" class="w-full sm:w-auto">
                Manage Inventory
            </PrimaryButton>
        </div>
    </div>
</template>

