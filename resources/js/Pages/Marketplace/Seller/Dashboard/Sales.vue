<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SalesChart from '@/Components/Marketplace/SalesChart.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    chartData: {
        type: Array,
        default: () => [],
    },
    topProducts: {
        type: Array,
        default: () => [],
    },
    revenueBreakdown: {
        type: Array,
        default: () => [],
    },
    period: {
        type: String,
        default: 'daily',
    },
    days: {
        type: Number,
        default: 30,
    },
});

const filterForm = useForm({
    period: props.period || 'daily',
    days: props.days || 30,
});

const applyFilters = () => {
    filterForm.get(route('marketplace.seller.dashboard.sales'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Sales Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Sales Analytics
                </h2>
                <Link
                    :href="route('marketplace.seller.dashboard.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Period
                            </label>
                            <select
                                v-model="filterForm.period"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Days
                            </label>
                            <select
                                v-model="filterForm.days"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option :value="7">Last 7 days</option>
                                <option :value="30">Last 30 days</option>
                                <option :value="90">Last 90 days</option>
                                <option :value="365">Last year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Sales</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ stats.total_sales || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats.total_revenue || 0) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Order Value</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats.average_order_value || 0) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Conversion Rate</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ stats.conversion_rate ? stats.conversion_rate.toFixed(2) : '0.00' }}%
                        </p>
                    </div>
                </div>

                <!-- Sales Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Sales Chart
                    </h3>
                    <SalesChart v-if="chartData && chartData.length > 0" :data="chartData" />
                    <div v-else class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
                        No sales data available
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Top Products
                    </h3>
                    <div v-if="topProducts && topProducts.length > 0" class="space-y-4">
                        <div
                            v-for="(product, index) in topProducts"
                            :key="product.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex items-center gap-4">
                                <span class="text-2xl font-bold text-gray-400 dark:text-gray-500">
                                    #{{ index + 1 }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ product.sales_count || 0 }} sales
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(product.revenue || 0) }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Revenue
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No products data available
                    </div>
                </div>

                <!-- Revenue Breakdown by Category -->
                <div v-if="revenueBreakdown && revenueBreakdown.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Revenue Breakdown by Category
                    </h3>
                    <div class="space-y-4">
                        <div
                            v-for="item in revenueBreakdown"
                            :key="item.category || 'uncategorized'"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ item.category || 'Uncategorized' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.count || 0 }} products
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(item.revenue || 0) }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.percentage ? item.percentage.toFixed(1) : '0.0' }}% of total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

