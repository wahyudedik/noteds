<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const filterForm = useForm({
    status: props.filters?.status || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    filterForm.get(route('marketplace.orders.index'), {
        preserveState: true,
    });
};

const clearFilters = () => {
    filterForm.status = 'all';
    filterForm.date_from = '';
    filterForm.date_to = '';
    applyFilters();
};
</script>

<template>
    <Head title="My Orders" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                My Orders
            </h2>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select
                                v-model="filterForm.status"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                            <input
                                v-model="filterForm.date_from"
                                type="date"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                            <input
                                v-model="filterForm.date_to"
                                type="date"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Order #</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Date</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-white">
                                        <span class="sm:hidden font-semibold">#</span>{{ order.order_number }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900 dark:text-white">
                                        <div class="max-w-[150px] sm:max-w-none truncate sm:whitespace-normal">
                                            {{ order.product?.name }}
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-white">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(order.total) }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                order.status === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                                order.status === 'paid' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' :
                                                order.status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200' :
                                                'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                                            ]"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                        {{ new Date(order.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <Link
                                            :href="route('marketplace.orders.show', order.id)"
                                            class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium min-h-[44px] flex items-center"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="6" class="px-3 sm:px-6 py-8 text-center text-sm sm:text-base text-gray-500 dark:text-gray-400">
                                        No orders found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <nav class="flex justify-center">
                            <div class="flex space-x-2">
                                <Link
                                    v-for="link in orders.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    :class="[
                                        'px-4 py-2 rounded-lg',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
