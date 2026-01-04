<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    refunds: Object,
    filters: Object,
});

const selectedWalletType = ref(props.filters?.wallet_type || 'all');
const selectedType = ref(props.filters?.type || 'all');
const searchQuery = ref(props.filters?.search || '');

const filterByWalletType = (walletType) => {
    selectedWalletType.value = walletType || 'all';
    router.get(route('admin.refunds.index'), {
        wallet_type: walletType || null,
        type: selectedType.value !== 'all' ? selectedType.value : null,
        search: searchQuery.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByType = (type) => {
    selectedType.value = type || 'all';
    router.get(route('admin.refunds.index'), {
        wallet_type: selectedWalletType.value !== 'all' ? selectedWalletType.value : null,
        type: type || null,
        search: searchQuery.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const search = () => {
    router.get(route('admin.refunds.index'), {
        wallet_type: selectedWalletType.value !== 'all' ? selectedWalletType.value : null,
        type: selectedType.value !== 'all' ? selectedType.value : null,
        search: searchQuery.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    router.get(route('admin.refunds.index'), {
        wallet_type: selectedWalletType.value !== 'all' ? selectedWalletType.value : null,
        type: selectedType.value !== 'all' ? selectedType.value : null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manage Refunds" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Manage Refunds
                </h2>
                <Link
                    :href="route('admin.refunds.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                >
                    Create Refund
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <!-- Wallet Type Filters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Wallet Type
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByWalletType(null)"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedWalletType === 'all'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="filterByWalletType('creator')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedWalletType === 'creator'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Creator
                            </button>
                            <button
                                @click="filterByWalletType('marketplace')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedWalletType === 'marketplace'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Marketplace
                            </button>
                        </div>
                    </div>

                    <!-- Type Filters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByType(null)"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'all'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="filterByType('refund')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'refund'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Refund
                            </button>
                            <button
                                @click="filterByType('adjustment')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'adjustment'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Adjustment
                            </button>
                        </div>
                    </div>

                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Search User
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by name or email..."
                                @keyup.enter="search"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button
                                @click="search"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                            >
                                Search
                            </button>
                            <button
                                v-if="searchQuery"
                                @click="clearSearch"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Refunds Table -->
                <div v-if="refunds?.data && refunds.data.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Wallet Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="refund in refunds.data" :key="refund.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(refund.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ refund.user?.name }}<br>
                                    <span class="text-gray-500 text-xs">{{ refund.user?.email }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            refund.wallet_type === 'creator' 
                                                ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                                : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                        ]"
                                    >
                                        {{ refund.wallet_type === 'creator' ? 'Creator' : 'Marketplace' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            refund.type === 'refund'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        ]"
                                    >
                                        {{ refund.type === 'refund' ? 'Refund' : 'Adjustment' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span :class="refund.type === 'refund' ? 'text-green-600' : 'text-red-600'">
                                        {{ refund.type === 'refund' ? '+' : '-' }}Rp {{ new Intl.NumberFormat('id-ID').format(refund.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ refund.reason || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ refund.admin?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link
                                        :href="route('admin.refunds.show', refund.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No refunds found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="refunds?.links && refunds.links.length > 3" class="flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="(link, index) in refunds.links"
                            :key="index"
                            :href="link.url || '#'"
                            :class="[
                                'px-4 py-2 rounded-lg transition-colors',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
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

