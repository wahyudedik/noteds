<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ledgerEntries: Object,
    filters: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const searchQuery = ref(props.filters?.search || '');
const selectedWalletType = ref(props.filters?.wallet_type || 'all');
const selectedReason = ref(props.filters?.reason || 'all');

const search = () => {
    router.get(route('admin.wallets.ledger'), {
        search: searchQuery.value || null,
        wallet_type: selectedWalletType.value !== 'all' ? selectedWalletType.value : null,
        reason: selectedReason.value !== 'all' ? selectedReason.value : null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getReasonBadgeClass = (reason) => {
    const classes = {
        reward: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        fee: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        refund: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        topup: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        withdrawal: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        campaign_lock: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        campaign_unlock: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    };
    return classes[reason] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};
</script>

<template>
    <Head title="Ledger Entries" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Ledger Entries
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                @keyup.enter="search"
                                type="text"
                                placeholder="Transaction ID..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Wallet Type</label>
                            <select
                                v-model="selectedWalletType"
                                @change="search"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="all">All</option>
                                <option value="creator">Creator</option>
                                <option value="campaign">Campaign</option>
                                <option value="clipper">Clipper</option>
                                <option value="platform">Platform</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason</label>
                            <select
                                v-model="selectedReason"
                                @change="search"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="all">All</option>
                                <option value="reward">Reward</option>
                                <option value="fee">Fee</option>
                                <option value="refund">Refund</option>
                                <option value="topup">Top Up</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="campaign_lock">Campaign Lock</option>
                                <option value="campaign_unlock">Campaign Unlock</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="search"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div v-if="ledgerEntries?.data && ledgerEntries.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">From</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="entry in ledgerEntries.data" :key="entry.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                        {{ entry.transaction_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ entry.from_wallet_type || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ entry.to_wallet_type || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(entry.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="['px-2 py-1 text-xs font-medium rounded-full', getReasonBadgeClass(entry.reason)]"
                                        >
                                            {{ entry.reason.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(entry.created_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        No ledger entries found.
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="ledgerEntries?.links && ledgerEntries.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ ledgerEntries.from }} to {{ ledgerEntries.to }} of {{ ledgerEntries.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <a
                            v-for="link in ledgerEntries.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 text-sm rounded-md',
                                link.active 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

