<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    wallet: Object,
    history: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getTransactionTypeClass = (reason) => {
    const classes = {
        reward: 'text-green-600 dark:text-green-400',
        fee: 'text-red-600 dark:text-red-400',
        withdrawal: 'text-orange-600 dark:text-orange-400',
        refund: 'text-blue-600 dark:text-blue-400',
    };
    return classes[reason] || 'text-gray-600 dark:text-gray-400';
};

const getAmountDisplay = (entry) => {
    // Determine if this is an incoming or outgoing transaction
    const isIncoming = entry.to_wallet_type === 'clipper' && entry.to_wallet_id === wallet?.id;
    const amount = parseFloat(entry.amount);
    return {
        amount: amount,
        sign: isIncoming ? '+' : '-',
        class: isIncoming ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
    };
};
</script>

<template>
    <Head title="Clipper Wallet History" />

    <ClipperLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clipper Wallet History
                </h2>
                <Link
                    :href="route('clipper.wallet.clipper')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    Back to Wallet
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Wallet Balance Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Balance Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Available Balance</div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(wallet?.balance_available) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Locked Balance</div>
                            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                                Rp {{ formatCurrency(wallet?.balance_locked) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Transaction History</h3>

                    <div v-if="history && history.length > 0" class="overflow-x-auto">
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
                                <tr v-for="entry in history" :key="entry.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(entry.created_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="['font-medium capitalize', getTransactionTypeClass(entry.reason)]">
                                            {{ entry.reason.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="getAmountDisplay(entry).class">
                                        {{ getAmountDisplay(entry).sign }}Rp {{ formatCurrency(getAmountDisplay(entry).amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div>
                                            <div>{{ entry.reference_type || '-' }}</div>
                                            <div v-if="entry.metadata" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                {{ JSON.stringify(entry.metadata) }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <p>No transaction history found.</p>
                        <Link
                            :href="route('clipper.wallet.clipper')"
                            class="mt-4 inline-block text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Back to Wallet
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

