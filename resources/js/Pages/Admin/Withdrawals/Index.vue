<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    withdrawals: Object,
    filters: Object,
});

const selectedStatus = ref(props.filters?.status || 'all');
const selectedUserType = ref(props.filters?.user_type || 'all');

const filterByStatus = (status) => {
    selectedStatus.value = status || 'all';
    router.get(route('admin.withdrawals.index'), {
        status: status || null,
        user_type: selectedUserType.value !== 'all' ? selectedUserType.value : null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByUserType = (userType) => {
    selectedUserType.value = userType || 'all';
    router.get(route('admin.withdrawals.index'), {
        status: selectedStatus.value !== 'all' ? selectedStatus.value : null,
        user_type: userType || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manage Withdrawals" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Withdrawals
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <!-- Status Filters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByStatus(null)"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'all'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="filterByStatus('pending')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'pending'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Pending
                            </button>
                            <button
                                @click="filterByStatus('approved')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'approved'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Approved
                            </button>
                            <button
                                @click="filterByStatus('rejected')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'rejected'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Rejected
                            </button>
                            <button
                                @click="filterByStatus('completed')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'completed'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Completed
                            </button>
                        </div>
                    </div>

                    <!-- User Type Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            User Type
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByUserType(null)"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedUserType === 'all'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All Withdrawals
                            </button>
                            <button
                                @click="filterByUserType('clipper')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedUserType === 'clipper'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Creator Withdrawals
                            </button>
                            <button
                                @click="filterByUserType('creator')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedUserType === 'creator'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Brand Withdrawals
                            </button>
                            <button
                                @click="filterByUserType('seller')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedUserType === 'seller'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Marketplace Withdrawals
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Withdrawals Table -->
                <div v-if="withdrawals?.data && withdrawals.data.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ withdrawal.user?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            withdrawal.user_type === 'clipper' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                            withdrawal.user_type === 'creator' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        ]"
                                    >
                                        {{ withdrawal.user_type === 'clipper' ? 'Clipper' : withdrawal.user_type === 'creator' ? 'Creator' : 'Marketplace' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(withdrawal.amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{ withdrawal.method === 'bank_transfer' ? withdrawal.bank_name : withdrawal.ewallet_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            withdrawal.status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                            withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                            withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        ]"
                                    >
                                        {{ withdrawal.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ new Date(withdrawal.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link
                                        :href="route('admin.withdrawals.show', withdrawal.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        Review
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No withdrawals found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="withdrawals?.links && withdrawals.links.length > 3" class="flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="(link, index) in withdrawals.links"
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

