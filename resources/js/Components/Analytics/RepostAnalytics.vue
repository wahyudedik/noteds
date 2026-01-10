<script setup>
import { ref, computed } from 'vue';
import RepostTimeline from './RepostTimeline.vue';
import RepostBreakdown from './RepostBreakdown.vue';
import RepostList from '@/Components/Repost/RepostList.vue';

const props = defineProps({
    post: Object,
    breakdown: Object,
    engagement: Object,
    timeline: Array,
    reposters: Array,
});

const activeTab = ref('overview');
const dateRange = ref('30'); // days

const tabs = [
    { id: 'overview', label: 'Overview' },
    { id: 'timeline', label: 'Timeline' },
    { id: 'breakdown', label: 'Breakdown' },
    { id: 'reposters', label: 'Reposters' },
];

const exportData = () => {
    window.location.href = route('reposts.export', props.post.id);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Repost Analytics</h2>
            <button
                @click="exportData"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
            >
                Export Data
            </button>
        </div>

        <!-- Engagement Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Reposts</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ engagement?.total_reposts || 0 }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Unique Reposters</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ engagement?.unique_reposters || 0 }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Quote Reposts</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ engagement?.quote_reposts || 0 }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Avg per Day</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ engagement?.avg_reposts_per_day || 0 }}
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex gap-4">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'px-4 py-2 border-b-2 transition',
                        activeTab === tab.id
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div>
            <div v-if="activeTab === 'overview'" class="space-y-6">
                <RepostBreakdown :breakdown="breakdown" />
                <RepostTimeline :timeline="timeline" />
            </div>
            <div v-if="activeTab === 'timeline'">
                <RepostTimeline :timeline="timeline" />
            </div>
            <div v-if="activeTab === 'breakdown'">
                <RepostBreakdown :breakdown="breakdown" />
            </div>
            <div v-if="activeTab === 'reposters'">
                <RepostList
                    :reposts="reposters"
                    :post-id="post.id"
                />
            </div>
        </div>
    </div>
</template>

