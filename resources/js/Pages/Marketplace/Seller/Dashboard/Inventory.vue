<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    inventoryStatus: {
        type: Object,
        default: () => ({}),
    },
    lowStockProducts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Inventory Overview" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Inventory Overview
                </h2>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('marketplace.seller.inventory.index')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                    >
                        Manage Inventory
                    </Link>
                    <Link
                        :href="route('marketplace.seller.dashboard.index')"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Back to Dashboard
                    </Link>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Products</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ inventoryStatus.total_products || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Low Stock</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">
                            {{ inventoryStatus.low_stock_count || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Out of Stock</p>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-2">
                            {{ inventoryStatus.out_of_stock_count || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Stock Value</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            Rp {{ new Intl.NumberFormat('id-ID').format(inventoryStatus.total_stock_value || 0) }}
                        </p>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div v-if="lowStockProducts && lowStockProducts.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Low Stock Products
                        </h3>
                        <Link
                            :href="route('marketplace.seller.inventory.index', { filter: 'low_stock' })"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="product in lowStockProducts.slice(0, 10)"
                            :key="product.id"
                            class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
                        >
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ product.name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Stock: {{ product.stock || 0 }} units
                                    <span v-if="product.low_stock_threshold" class="ml-2">
                                        (Threshold: {{ product.low_stock_threshold }})
                                    </span>
                                </p>
                            </div>
                            <Link
                                :href="route('marketplace.seller.inventory.show', product.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                            >
                                Manage
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <Link
                        :href="route('marketplace.seller.inventory.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manage Inventory</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View and update all products</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('marketplace.seller.inventory.index', { filter: 'low_stock' })"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Low Stock Alerts</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View products needing restock</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

