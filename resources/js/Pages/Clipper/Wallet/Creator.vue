<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    wallet: Object,
    transactions: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getTransactionTypeClass = (reason) => {
    const classes = {
        topup: 'text-green-600 dark:text-green-400',
        campaign_lock: 'text-yellow-600 dark:text-yellow-400',
        campaign_unlock: 'text-blue-600 dark:text-blue-400',
        refund: 'text-green-600 dark:text-green-400',
        fee: 'text-red-600 dark:text-red-400',
    };
    return classes[reason] || 'text-gray-600 dark:text-gray-400';
};
</script>

<template>
    <Head title="Creator Wallet" />

    <ClipperLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Creator Wallet
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('clipper.top-ups.create')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Top Up
                    </Link>
                    <Link
                        v-if="wallet?.balance_available > 0"
                        :href="route('clipper.withdrawals.creator.create')"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        Withdraw
                    </Link>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Wallet Balance Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Balance Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Available Balance</div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(wallet?.balance_available) }}
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Available for campaigns
                            </p>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Locked Balance</div>
                            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                                Rp {{ formatCurrency(wallet?.balance_locked) }}
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Locked in active campaigns
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Transaction History</h3>
                        <Link
                            :href="route('clipper.wallet.creator.history')"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            View All
                        </Link>
                    </div>

                    <div v-if="transactions?.data && transactions.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="transaction in transactions.data.slice(0, 10)" :key="transaction.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(transaction.created_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="['font-medium capitalize', getTransactionTypeClass(transaction.reason)]">
                                            {{ transaction.reason.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="getTransactionTypeClass(transaction.reason)">
                                        {{ transaction.amount >= 0 ? '+' : '' }}Rp {{ formatCurrency(Math.abs(transaction.amount)) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ transaction.metadata?.description || transaction.reference_type || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No transactions yet.
                    </div>
                </div>

                <!-- Withdrawal History Link -->
                <div v-if="wallet?.balance_available > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold mb-1">Withdrawals</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                View and manage your withdrawal requests for remaining budget
                            </p>
                        </div>
                        <Link
                            :href="route('clipper.withdrawals.creator.index')"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                        >
                            View Withdrawals
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

