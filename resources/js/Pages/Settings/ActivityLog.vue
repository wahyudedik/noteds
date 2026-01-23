<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    activities: Object,
    activityTypes: Object,
    filters: Object,
});

const filterForm = useForm({
    activity_type: props.filters?.activity_type || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
});

const isLoading = ref(false);

const applyFilters = () => {
    isLoading.value = true;
    router.get(route('settings.activity-log'), filterForm.data(), {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const clearFilters = () => {
    filterForm.activity_type = '';
    filterForm.start_date = '';
    filterForm.end_date = '';
    filterForm.search = '';
    applyFilters();
};

const exportToCsv = () => {
    const params = new URLSearchParams();
    if (filterForm.activity_type) params.append('activity_type', filterForm.activity_type);
    if (filterForm.start_date) params.append('start_date', filterForm.start_date);
    if (filterForm.end_date) params.append('end_date', filterForm.end_date);
    if (filterForm.search) params.append('search', filterForm.search);

    window.location.href = route('settings.activity-log.export') + '?' + params.toString();
};

const getActivityTypeLabel = (type) => {
    return props.activityTypes[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Activity Log" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Activity Log
                </h2>
                <Link
                    :href="route('settings.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Settings
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <header class="flex items-center justify-between mb-6">
                    <div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            View your account activity history, including login history, profile changes, security changes, transactions, and withdrawals.
                        </p>
                    </div>
                    <PrimaryButton
                        @click="exportToCsv"
                        class="ml-4"
                    >
                        Export CSV
                    </PrimaryButton>
                </header>

        <!-- Filters -->
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Activity Type
                    </label>
                    <select
                        v-model="filterForm.activity_type"
                        @change="applyFilters"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                    >
                        <option value="">All Types</option>
                        <option v-for="(label, key) in activityTypes" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Start Date
                    </label>
                    <input
                        v-model="filterForm.start_date"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        End Date
                    </label>
                    <input
                        v-model="filterForm.end_date"
                        type="date"
                        @change="applyFilters"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Search
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Search activities..."
                            @keyup.enter="applyFilters"
                            class="w-full sm:flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                        />
                        <button
                            @click="applyFilters"
                            :disabled="isLoading"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 text-sm w-full sm:w-auto"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button
                    @click="clearFilters"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div v-if="!activities.data || activities.data.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                No activities found.
            </div>

            <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                <div
                    v-for="activity in activities.data"
                    :key="activity.id"
                    class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                    {{ getActivityTypeLabel(activity.activity_type) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ formatDate(activity.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                {{ activity.description }}
                            </p>
                            <p v-if="activity.action" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Action: {{ activity.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </p>
                            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <span v-if="activity.ip_address">
                                    IP: {{ activity.ip_address }}
                                </span>
                                <span v-if="activity.user_agent" class="truncate max-w-xs">
                                    {{ activity.user_agent }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="activities.links && activities.links.length > 3" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div v-if="activities.from && activities.to && activities.total" class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in activities.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : link.url
                                    ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

