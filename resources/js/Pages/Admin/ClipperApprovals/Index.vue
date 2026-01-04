<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    registrations: Object,
    filters: Object,
});

const selectedStatus = ref(props.filters?.status || 'all');

const filterByStatus = (status) => {
    selectedStatus.value = status;
    router.get(route('admin.clipper-approvals.index'), {
        status: status === 'all' ? null : status,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.pending;
};
</script>

<template>
    <Head title="Clipper Approvals" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Clipper Approvals
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="filterByStatus('all')"
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
                    </div>
                </div>

                <!-- Registrations Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Portfolio URL
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Skills
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Submitted At
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="registrations.data && registrations.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No clipper registrations found.
                                </td>
                            </tr>
                            <tr
                                v-for="registration in registrations.data"
                                :key="registration.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ registration.user?.name || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ registration.user?.email || 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <a
                                            v-if="registration.portfolio_url"
                                            :href="registration.portfolio_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 truncate max-w-xs block"
                                        >
                                            {{ registration.portfolio_url }}
                                        </a>
                                        <span v-else class="text-gray-400">-</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <div v-if="registration.skills && Array.isArray(registration.skills) && registration.skills.length > 0" class="flex flex-wrap gap-1">
                                            <span
                                                v-for="skill in registration.skills"
                                                :key="skill"
                                                class="inline-block px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded"
                                            >
                                                {{ skill }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-400">-</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            getStatusBadgeClass(registration.status)
                                        ]"
                                    >
                                        {{ registration.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(registration.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link
                                        :href="route('admin.clipper-approvals.show', registration.id)"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="registrations.links && registrations.links.length > 3" class="mt-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ registrations.from }} to {{ registrations.to }} of {{ registrations.total }} results
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in registrations.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-4 py-2 text-sm border rounded-lg',
                                    link.active
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
