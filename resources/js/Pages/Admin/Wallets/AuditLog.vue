<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    auditLogs: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const selectedAction = ref(props.filters?.action || 'all');
const selectedTargetType = ref(props.filters?.target_type || 'all');

const search = () => {
    router.get(route('admin.wallets.audit-log'), {
        search: searchQuery.value || null,
        action: selectedAction.value !== 'all' ? selectedAction.value : null,
        target_type: selectedTargetType.value !== 'all' ? selectedTargetType.value : null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getActionBadgeClass = (action) => {
    const classes = {
        approve_clip: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        reject_clip: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        adjust_reward: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        freeze_wallet: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        unfreeze_wallet: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        adjust_balance: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[action] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};
</script>

<template>
    <Head title="Audit Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Audit Logs
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
                                placeholder="User, Admin, Target ID..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                            <select
                                v-model="selectedAction"
                                @change="search"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="all">All</option>
                                <option value="approve_clip">Approve Clip</option>
                                <option value="reject_clip">Reject Clip</option>
                                <option value="adjust_reward">Adjust Reward</option>
                                <option value="freeze_wallet">Freeze Wallet</option>
                                <option value="unfreeze_wallet">Unfreeze Wallet</option>
                                <option value="adjust_balance">Adjust Balance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Type</label>
                            <select
                                v-model="selectedTargetType"
                                @change="search"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="all">All</option>
                                <option value="wallet">Wallet</option>
                                <option value="clip">Clip</option>
                                <option value="campaign">Campaign</option>
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

                <!-- Audit Logs Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div v-if="auditLogs?.data && auditLogs.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Admin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Target</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="log in auditLogs.data" :key="log.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(log.created_at).toLocaleDateString('id-ID', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="['px-2 py-1 text-xs font-medium rounded-full', getActionBadgeClass(log.action)]"
                                        >
                                            {{ log.action.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ log.user?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ log.admin?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ log.target_type }} #{{ log.target_id?.substring(0, 8) || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ log.notes || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        No audit logs found.
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="auditLogs?.links && auditLogs.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ auditLogs.from }} to {{ auditLogs.to }} of {{ auditLogs.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <a
                            v-for="link in auditLogs.links"
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

