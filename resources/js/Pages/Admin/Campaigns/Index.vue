<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    campaigns: Object,
    filters: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.draft;
};

const filterByStatus = (status) => {
    router.get(route('admin.campaigns.index'), { status: status || null }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manage Campaigns" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Campaigns
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-4 flex space-x-2">
                    <button
                        @click="filterByStatus(null)"
                        :class="['px-4 py-2 rounded-lg', !filters?.status ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('draft')"
                        :class="['px-4 py-2 rounded-lg', filters?.status === 'draft' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                    >
                        Draft
                    </button>
                    <button
                        @click="filterByStatus('active')"
                        :class="['px-4 py-2 rounded-lg', filters?.status === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                    >
                        Active
                    </button>
                    <button
                        @click="filterByStatus('completed')"
                        :class="['px-4 py-2 rounded-lg', filters?.status === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                    >
                        Completed
                    </button>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div v-if="campaigns?.data && campaigns.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Creator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Budget</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Views</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clips</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="campaign in campaigns.data" :key="campaign.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ campaign.title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ campaign.creator?.name || 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(campaign.max_budget) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(campaign.status)]"
                                        >
                                            {{ campaign.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(campaign.total_views) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ campaign.total_clips || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <Link
                                            :href="route('admin.campaigns.show', campaign.id)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        No campaigns found.
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="campaigns?.links && campaigns.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ campaigns.from }} to {{ campaigns.to }} of {{ campaigns.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-for="link in campaigns.links"
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

