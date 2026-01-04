<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const filterForm = useForm({
    type: props.filters?.type || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    filterForm.get(route('marketplace.wallet.transactions'), {
        preserveState: true,
    });
};

const clearFilters = () => {
    filterForm.type = 'all';
    filterForm.date_from = '';
    filterForm.date_to = '';
    applyFilters();
};
</script>

<template>
    <Head title="Transaction History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Transaction History
                </h2>
                <Link
                    :href="route('marketplace.wallet.index')"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    ← Back to Wallet
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select
                                v-model="filterForm.type"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All Types</option>
                                <option value="sale">Sales</option>
                                <option value="withdrawal">Withdrawals</option>
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
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="transaction in transactions.data" :key="transaction.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(transaction.created_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            (transaction.type === 'sale' || transaction.type === 'deposit' || transaction.type === 'refund') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ transaction.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ transaction.description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                                    :class="(transaction.type === 'sale' || transaction.type === 'deposit' || transaction.type === 'refund') ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ (transaction.type === 'sale' || transaction.type === 'deposit' || transaction.type === 'refund') ? '+' : '-' }}Rp {{ new Intl.NumberFormat('id-ID').format(transaction.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(transaction.balance_after) }}
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No transactions found
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="transactions.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <nav class="flex justify-center">
                            <div class="flex space-x-2">
                                <Link
                                    v-for="link in transactions.links"
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

