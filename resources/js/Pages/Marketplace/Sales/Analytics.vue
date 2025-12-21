<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SalesChart from '@/Components/Marketplace/SalesChart.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    chart_data: Array,
    top_products: Array,
    sales_by_category: Array,
    period: String,
    days: Number,
});
</script>

<template>
    <Head title="Sales Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Sales Analytics
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Sales</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ stats?.total_sales || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Revenue</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats?.total_revenue || 0) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Average Order Value</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats?.average_order_value || 0) }}
                        </p>
                    </div>
                </div>

                <!-- Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Sales Chart</h3>
                    <SalesChart :data="chart_data" />
                </div>

                <!-- Top Products -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Top Products</h3>
                    <div class="space-y-4">
                        <div
                            v-for="(product, index) in top_products"
                            :key="product.id"
                            class="flex justify-between items-center"
                        >
                            <div>
                                <span class="font-semibold">#{{ index + 1 }}</span>
                                <span class="ml-2">{{ product.name }}</span>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ product.sales_count }} sales</p>
                                <p class="text-sm text-gray-500">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(product.revenue) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

