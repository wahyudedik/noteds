<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: { type: Object, default: () => ({}) },
    metrics: { type: Object, default: () => ({ total: 0, zero_result: 0, avg_duration_ms: 0, success_rate: 0 }) },
    topQueries: { type: Array, default: () => [] },
    timelineAll: { type: Array, default: () => [] },
    timelineZero: { type: Array, default: () => [] },
});

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const type = ref(props.filters.type || 'all');
const segment = ref(props.filters.segment || 'all');
const period = ref(props.filters.period || 'daily');

const applyFilters = () => {
    router.get(route('admin.search.analytics'), {
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
        type: type.value || 'all',
        segment: segment.value || 'all',
        period: period.value || 'daily',
    }, { preserveState: true, preserveScroll: true });
};

const exportCsv = () => {
    window.location.href = route('admin.search.analytics.export', { format: 'csv', date_from: dateFrom.value, date_to: dateTo.value });
};
const exportPdf = () => {
    window.location.href = route('admin.search.analytics.export', { format: 'pdf', date_from: dateFrom.value, date_to: dateTo.value });
};
</script>

<template>
    <Head title="Search Analytics" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Search Analytics</h2>
                <div class="flex gap-2">
                    <button @click="exportCsv" class="px-3 py-1.5 bg-blue-600 text-white rounded-md">Export CSV</button>
                    <button @click="exportPdf" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md">Export PDF</button>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                            <input type="date" v-model="dateFrom" @change="applyFilters"
                                   class="block w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                            <input type="date" v-model="dateTo" @change="applyFilters"
                                   class="block w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select v-model="type" @change="applyFilters"
                                    class="block w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All</option>
                                <option value="posts">Posts</option>
                                <option value="users">Users</option>
                                <option value="articles">Articles</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Segment</label>
                            <select v-model="segment" @change="applyFilters"
                                    class="block w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period</label>
                            <select v-model="period" @change="applyFilters"
                                    class="block w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Searches</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.total.toLocaleString('id-ID') }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Zero Result</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.zero_result.toLocaleString('id-ID') }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Avg Duration (ms)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.avg_duration_ms }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Success Rate</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.success_rate }}%</div>
                    </div>
                </div>

                <!-- Top Queries -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Top Queries</h3>
                    <div v-if="topQueries.length > 0" class="space-y-2">
                        <div v-for="item in topQueries" :key="item.query" class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ item.query }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.count }}</span>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-600 dark:text-gray-400">No data</div>
                </div>

                <!-- Timeline Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Search Volume</h3>
                        <div class="relative h-48">
                            <svg viewBox="0 0 600 200" class="w-full h-full">
                                <polyline
                                    v-if="timelineAll.length > 1"
                                    :points="timelineAll.map((p, i) => `${(i/(timelineAll.length-1))*580+10},${180 - (p.count / Math.max(...timelineAll.map(x => x.count),1))*160}` ).join(' ')"
                                    fill="none" stroke="#3B82F6" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Zero Result Trend</h3>
                        <div class="relative h-48">
                            <svg viewBox="0 0 600 200" class="w-full h-full">
                                <polyline
                                    v-if="timelineZero.length > 1"
                                    :points="timelineZero.map((p, i) => `${(i/(timelineZero.length-1))*580+10},${180 - (p.count / Math.max(...timelineZero.map(x => x.count),1))*160}` ).join(' ')"
                                    fill="none" stroke="#EF4444" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
