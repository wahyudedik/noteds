<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    withdrawals: Object,
    filters: Object,
});

const filterByStatus = (status) => {
    router.get(route('admin.withdrawals.index'), { status }, {
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
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-4 flex space-x-2">
                    <button
                        @click="filterByStatus(null)"
                        :class="['px-4 py-2 rounded-lg', !filters?.status ? 'bg-blue-600 text-white' : 'bg-gray-200']"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('pending')"
                        :class="['px-4 py-2 rounded-lg', filters?.status === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200']"
                    >
                        Pending
                    </button>
                    <button
                        @click="filterByStatus('approved')"
                        :class="['px-4 py-2 rounded-lg', filters?.status === 'approved' ? 'bg-blue-600 text-white' : 'bg-gray-200']"
                    >
                        Approved
                    </button>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
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
                                            withdrawal.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                            withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

