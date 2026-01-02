<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import ViewValidationStatus from '@/Components/Clipper/ViewValidationStatus.vue';

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

// Real-time tracking
const liveViews = ref({
    total_views: props.campaign?.total_views || 0,
    valid_views: 0,
    invalid_views: 0,
    last_updated: null,
});
const validationData = ref(null);
const isPolling = ref(false);
const pollInterval = ref(null);
const shouldPoll = computed(() => {
    return props.campaign && ['active', 'paused'].includes(props.campaign.status);
});

const fetchLiveViews = async () => {
    if (!shouldPoll.value || !props.campaign) return;
    
    try {
        const response = await fetch(route('clipper.campaigns.analytics.live', props.campaign.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            liveViews.value = {
                total_views: data.total_views || 0,
                valid_views: data.valid_views || 0,
                invalid_views: data.invalid_views || 0,
                last_updated: data.last_updated ? new Date(data.last_updated) : new Date(),
            };
        }
    } catch (error) {
        console.error('Error fetching live views:', error);
    }
};

const fetchValidationDetails = async () => {
    if (!shouldPoll.value || !props.campaign) return;
    
    try {
        const response = await fetch(route('clipper.campaigns.analytics.validation', props.campaign.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            validationData.value = {
                total_views: data.total_views || 0,
                valid_views: data.total_valid_views || 0,
                invalid_views: data.total_invalid_views || 0,
                stability_score: data.average_stability_score,
                fraud_detected: data.clips_with_fraud > 0,
                validation_rate: data.validation_rate || 0,
            };
        }
    } catch (error) {
        console.error('Error fetching validation details:', error);
    }
};

const renderChart = () => {
    // Simple bar chart implementation
    // In production, use Chart.js or similar library
    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx || !chartCanvas.value) return;

    const container = chartCanvas.value.parentElement;
    if (!container) return;
    
    const rect = container.getBoundingClientRect();
    const width = rect.width;
    const height = rect.height;

    const data = props.viewsChartData;
    const labels = Object.keys(data).sort();
    const values = labels.map(date => data[date] || 0);
    const maxValue = Math.max(...values, 1);

    if (labels.length === 0) {
        // Clear canvas if no data
        ctx.clearRect(0, 0, width, height);
        return;
    }

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Draw bars
    const barWidth = width / labels.length;
    const barMaxHeight = height - 40;
    const padding = Math.max(2, barWidth * 0.1); // 10% padding, minimum 2px

    values.forEach((value, index) => {
        const barHeight = maxValue > 0 ? (value / maxValue) * barMaxHeight : 0;
        const x = index * barWidth;
        const y = height - barHeight - 20;

        ctx.fillStyle = '#3B82F6';
        ctx.fillRect(x + padding, y, barWidth - (padding * 2), barHeight);
    });
};

const resizeChart = () => {
    if (!chartCanvas.value) return;
    
    const container = chartCanvas.value.parentElement;
    if (!container) return;
    
    const rect = container.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    
    // Set actual size in memory (scaled for DPR)
    chartCanvas.value.width = rect.width * dpr;
    chartCanvas.value.height = rect.height * dpr;
    
    // Scale the canvas back down using CSS
    chartCanvas.value.style.width = rect.width + 'px';
    chartCanvas.value.style.height = rect.height + 'px';
    
    // Scale the drawing context so everything draws at the correct size
    const ctx = chartCanvas.value.getContext('2d');
    ctx.scale(dpr, dpr);
    
    // Re-render chart with new dimensions
    renderChart();
};

onMounted(() => {
    if (props.viewsChartData && Object.keys(props.viewsChartData).length > 0 && chartCanvas.value) {
        resizeChart();
        window.addEventListener('resize', resizeChart);
    }
    
    if (shouldPoll.value) {
        isPolling.value = true;
        fetchLiveViews();
        fetchValidationDetails();
        
        pollInterval.value = setInterval(() => {
            fetchLiveViews();
            fetchValidationDetails();
        }, 30000);
    }
});

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
    isPolling.value = false;
    window.removeEventListener('resize', resizeChart);
});

watch(() => props.viewsChartData, () => {
    if (props.viewsChartData && Object.keys(props.viewsChartData).length > 0 && chartCanvas.value) {
        resizeChart();
    }
}, { deep: true });

watch(() => props.campaign, (newCampaign) => {
    if (newCampaign && ['active', 'paused'].includes(newCampaign.status) && !pollInterval.value) {
        isPolling.value = true;
        fetchLiveViews();
        fetchValidationDetails();
        pollInterval.value = setInterval(() => {
            fetchLiveViews();
            fetchValidationDetails();
        }, 30000);
    } else if ((!newCampaign || !['active', 'paused'].includes(newCampaign.status)) && pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
        isPolling.value = false;
    }
}, { deep: true });
</script>

<template>
    <Head :title="campaign ? `${campaign.title} - Analytics` : 'Campaign Analytics'" />

    <ClipperLayout>
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
                        <div class="flex items-center gap-2">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                            <span
                                v-if="isPolling"
                                class="px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded"
                                title="Auto-refreshing every 30 seconds"
                            >
                                🔄 Live
                            </span>
                        </div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ formatCurrency(liveViews.total_views || campaign.total_views || 0) }}
                        </div>
                        <div v-if="liveViews.last_updated" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Updated: {{ new Date(liveViews.last_updated).toLocaleTimeString('id-ID') }}
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

                <!-- View Validation Status -->
                <ViewValidationStatus 
                    v-if="campaign && validationData"
                    :validation-data="validationData"
                    :show-details="true"
                />

                <!-- Views Chart -->
                <div v-if="campaign && viewsChartData" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold mb-4">Views Over Time</h3>
                    <div class="relative h-48 sm:h-64 overflow-hidden">
                        <canvas 
                            ref="chartCanvas" 
                            class="w-full h-full"
                        ></canvas>
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
    </ClipperLayout>
</template>

