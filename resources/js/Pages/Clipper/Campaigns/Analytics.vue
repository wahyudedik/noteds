<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    overallStats: {
        type: Object,
        default: () => ({}),
    },
    campaigns: {
        type: Array,
        default: () => [],
    },
    campaign: {
        type: Object,
        default: null,
    },
    viewsChartData: {
        type: Object,
        default: () => ({}),
    },
    topClips: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
    if (props.viewsChartData && Object.keys(props.viewsChartData).length > 0 && chartCanvas.value) {
        // Simple chart rendering (can be enhanced with Chart.js or similar)
        renderChart();
    }
});

const renderChart = () => {
    // Simple bar chart implementation
    // In production, use Chart.js or similar library
    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx) return;

    const data = props.viewsChartData;
    const labels = Object.keys(data).sort();
    const values = labels.map(date => data[date] || 0);
    const maxValue = Math.max(...values, 1);

    // Clear canvas
    ctx.clearRect(0, 0, chartCanvas.value.width, chartCanvas.value.height);

    // Draw bars
    const barWidth = chartCanvas.value.width / labels.length;
    const barMaxHeight = chartCanvas.value.height - 40;

    values.forEach((value, index) => {
        const barHeight = (value / maxValue) * barMaxHeight;
        const x = index * barWidth;
        const y = chartCanvas.value.height - barHeight - 20;

        ctx.fillStyle = '#3B82F6';
        ctx.fillRect(x + 5, y, barWidth - 10, barHeight);
    });
};
</script>

<template>
    <Head :title="campaign ? `${campaign.title} - Analytics` : 'Campaign Analytics'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="campaign ? route('clipper.campaigns.show', campaign.id) : route('clipper.campaigns.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ campaign ? `${campaign.title} - Analytics` : 'Campaign Analytics Dashboard' }}
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Overall Stats (if viewing all campaigns) -->
                <div v-if="!campaign && overallStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Campaigns</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ overallStats.total_campaigns || 0 }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ formatCurrency(overallStats.total_views || 0) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Spent</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            Rp {{ formatCurrency(overallStats.total_spent || 0) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Average ROI</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ (overallStats.average_roi || 0).toFixed(2) }}x
                        </div>
                    </div>
                </div>

                <!-- Campaign-specific Stats -->
                <div v-if="campaign" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ formatCurrency(campaign.total_views || 0) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Clips</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ campaign.total_clips || 0 }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Spent</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            Rp {{ formatCurrency(campaign.total_spent || 0) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-sm text-gray-500 dark:text-gray-400">ROI</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ (campaign.roi || 0).toFixed(2) }}x
                        </div>
                    </div>
                </div>

                <!-- Views Chart -->
                <div v-if="campaign && viewsChartData" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Views Over Time</h3>
                    <div class="h-64">
                        <canvas ref="chartCanvas" width="800" height="200"></canvas>
                    </div>
                </div>

                <!-- Top Performing Clips -->
                <div v-if="topClips && topClips.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Top Performing Clips</h3>
                    <div class="space-y-4">
                        <div
                            v-for="(clip, index) in topClips"
                            :key="clip.id"
                            class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                        >
                            <div class="flex items-center gap-4">
                                <div class="text-2xl font-bold text-gray-400">#{{ index + 1 }}</div>
                                <div>
                                    <Link
                                        :href="route('clipper.clips.show', clip.id)"
                                        class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        Clip #{{ clip.id.substring(0, 8) }}
                                    </Link>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatCurrency(clip.valid_views) }} views • Rp {{ formatCurrency(clip.approved_reward) }} reward
                                    </div>
                                </div>
                            </div>
                            <Link
                                :href="route('clipper.clips.show', clip.id)"
                                class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                            >
                                View
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Campaign List (if viewing all campaigns) -->
                <div v-if="!campaign && campaigns && campaigns.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">All Campaigns</h3>
                    <div class="space-y-4">
                        <div
                            v-for="camp in campaigns"
                            :key="camp.id"
                            class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div>
                                <Link
                                    :href="route('clipper.campaigns.analytics.show', camp.id)"
                                    class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                >
                                    {{ camp.title }}
                                </Link>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ formatCurrency(camp.total_views) }} views • Rp {{ formatCurrency(camp.total_spent) }} spent
                                </div>
                            </div>
                            <Link
                                :href="route('clipper.campaigns.analytics.show', camp.id)"
                                class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                            >
                                View Analytics
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

