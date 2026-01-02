<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    currentBalance: {
        type: [Number, String],
        default: 0,
    },
    totalEarnings: {
        type: [Number, String],
        default: 0,
    },
    pendingWithdrawal: {
        type: [Number, String],
        default: 0,
    },
    recentTransactions: {
        type: Array,
        default: () => [],
    },
    totalSales: {
        type: [Number, String],
        default: 0,
    },
    productsSold: {
        type: [Number, String],
        default: 0,
    },
    averageOrderValue: {
        type: [Number, String],
        default: 0,
    },
});

// Convert string values to numbers
const currentBalance = computed(() => Number(props.currentBalance) || 0);
const totalEarnings = computed(() => Number(props.totalEarnings) || 0);
const pendingWithdrawal = computed(() => Number(props.pendingWithdrawal) || 0);
const totalSales = computed(() => Number(props.totalSales) || 0);
const productsSold = computed(() => Number(props.productsSold) || 0);
const averageOrderValue = computed(() => Number(props.averageOrderValue) || 0);
</script>

<template>
    <Head title="My Wallet" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                My Wallet
            </h2>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:space-y-6">
                <!-- Balance Card -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <p class="text-blue-100 text-xs sm:text-sm font-medium mb-1">Current Balance</p>
                            <p class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">
                                Rp {{ new Intl.NumberFormat('id-ID').format(currentBalance) }}
                            </p>
                            <p class="text-blue-100 text-xs sm:text-sm mt-2">
                                Total Earnings: Rp {{ new Intl.NumberFormat('id-ID').format(totalEarnings) }}
                            </p>
                        </div>
                        <svg class="h-12 w-12 sm:h-16 sm:w-16 text-blue-300 opacity-50 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div v-if="pendingWithdrawal > 0" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-blue-500">
                        <p class="text-xs sm:text-sm text-blue-100">Pending Withdrawal: Rp {{ new Intl.NumberFormat('id-ID').format(pendingWithdrawal) }}</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                            {{ totalSales }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total Sales
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                            {{ productsSold }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Products Sold
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ new Intl.NumberFormat('id-ID').format(averageOrderValue) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Avg Order Value
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            :href="route('marketplace.wallet.transactions')"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            View All Transactions
                        </Link>
                        <Link
                            :href="route('marketplace.wallet.sales')"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            View Sales
                        </Link>
                        <Link
                            :href="route('marketplace.withdrawals.create')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Withdraw Funds
                        </Link>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                        <Link
                            :href="route('marketplace.wallet.transactions')"
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            View All →
                        </Link>
                    </div>
                    <div v-if="recentTransactions && recentTransactions.length > 0">
                        <div class="space-y-3">
                            <div
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ transaction.description }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(transaction.created_at).toLocaleString() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        :class="[
                                            'font-semibold',
                                            transaction.type === 'sale' ? 'text-green-600' : 'text-red-600'
                                        ]"
                                    >
                                        {{ transaction.type === 'sale' ? '+' : '-' }}Rp {{ new Intl.NumberFormat('id-ID').format(transaction.amount) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Balance: Rp {{ new Intl.NumberFormat('id-ID').format(transaction.balance_after) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No transactions yet
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

