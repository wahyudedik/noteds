<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import ViewValidationStatus from '@/Components/Clipper/ViewValidationStatus.vue';
import RealTimeViewCounter from '@/Components/Clipper/RealTimeViewCounter.vue';
import ViewHistoryChart from '@/Components/Clipper/ViewHistoryChart.vue';
import ValidationTimeline from '@/Components/Clipper/ValidationTimeline.vue';
import FraudDetectionDetails from '@/Components/Clipper/FraudDetectionDetails.vue';

const props = defineProps({
    clip: Object,
    viewTracking: {
        type: Array,
        default: () => [],
    },
});

const clipData = ref(props.clip);
const viewTrackingData = ref(props.viewTracking || []);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[status] || classes.pending;
};

const shouldPoll = computed(() => {
    return clipData.value.status === 'pending' || clipData.value.status === 'approved';
});

const chartCanvas = ref(null);
let pollInterval = null;

const validationData = ref(null);

const fetchClipStatus = async () => {
    if (!shouldPoll.value) return;

    try {
        const response = await fetch(route('clipper.clips.status', clipData.value.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            // Update clip data with new status and views
            if (data.status) {
                clipData.value.status = data.status;
            }
            if (data.views) {
                clipData.value.valid_views = data.views.valid || clipData.value.valid_views;
                clipData.value.total_views = data.views.total || clipData.value.total_views;
            }
            if (data.rewards) {
                if (data.rewards.approved) {
                    clipData.value.approved_reward = data.rewards.approved;
                }
                if (data.rewards.pending) {
                    clipData.value.pending_reward = data.rewards.pending;
                }
            }
        }
    } catch (error) {
        console.error('Error fetching clip status:', error);
    }
};

const fetchValidationStatus = async () => {
    if (!shouldPoll.value) return;

    try {
        const response = await fetch(route('clipper.clips.validation', clipData.value.id), {
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
                valid_views: data.valid_views || 0,
                invalid_views: data.invalid_views || 0,
                stability_score: data.stability_score,
                fraud_detected: data.fraud_detected || false,
                validation_rate: data.validation_rate || 0,
                validation_history: data.validation_history || [],
            };
        }
    } catch (error) {
        console.error('Error fetching validation status:', error);
    }
};

onMounted(() => {
    if (props.viewTracking && props.viewTracking.length > 0 && chartCanvas.value) {
        renderChart();
    }

    // Fetch validation status immediately
    fetchValidationStatus();

    // Start polling if clip is pending or approved
    if (shouldPoll.value) {
        pollInterval = setInterval(() => {
            fetchClipStatus();
            fetchValidationStatus();
        }, 30000); // Poll every 30 seconds
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

// Watch for clip status changes to stop polling if needed
watch(() => clipData.value.status, (newStatus) => {
    if (newStatus !== 'pending' && newStatus !== 'approved' && pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    } else if ((newStatus === 'pending' || newStatus === 'approved') && !pollInterval) {
        pollInterval = setInterval(() => {
            fetchClipStatus();
            fetchValidationStatus();
        }, 30000);
    }
});

// Watch for viewTracking changes to re-render chart
watch(() => props.viewTracking, (newTracking) => {
    viewTrackingData.value = newTracking || [];
    if (viewTrackingData.value.length > 0 && chartCanvas.value) {
        renderChart();
    }
}, { deep: true });

const renderChart = () => {
    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx || !viewTrackingData.value || viewTrackingData.value.length === 0) return;

    const data = viewTrackingData.value;
    const maxViews = Math.max(...data.map(t => t.views_count), 1);
    const width = chartCanvas.value.width;
    const height = chartCanvas.value.height;
    const padding = 40;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Draw axes
    ctx.strokeStyle = '#9CA3AF';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    // Draw line chart
    if (data.length > 1) {
        ctx.strokeStyle = '#3B82F6';
        ctx.lineWidth = 2;
        ctx.beginPath();

        data.forEach((track, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = height - padding - (track.views_count / maxViews) * chartHeight;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });

        ctx.stroke();

        // Draw points
        ctx.fillStyle = '#3B82F6';
        data.forEach((track, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = height - padding - (track.views_count / maxViews) * chartHeight;

            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
};
</script>

<template>
    <Head title="Clip Details" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.clips.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Clips
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clip Details
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Clip Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    Clip #{{ clipData.id.substring(0, 8) }}
                                </h1>
                                <span
                                    :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(clipData.status)]"
                                >
                                    {{ clipData.status }}
                                </span>
                                <span
                                    v-if="shouldPoll"
                                    class="px-2 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded"
                                    title="Auto-refreshing every 30 seconds"
                                >
                                    🔄 Live
                                </span>
                            </div>

                            <div v-if="clipData.campaign" class="mb-4">
                                <Link
                                    :href="route('clipper.campaigns.show', clipData.campaign.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Campaign: {{ clipData.campaign.title }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Clip Details -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Platform</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white capitalize mt-1">
                                {{ clip.platform }}
                            </div>
                        </div>
                        <div>
                            <RealTimeViewCounter
                                v-if="shouldPoll"
                                :initial-views="clipData.total_views || 0"
                                :endpoint="route('clipper.clips.views.live', clipData.id)"
                                :poll-interval="30000"
                                :show-growth-rate="true"
                                :enabled="shouldPoll"
                            />
                            <div v-else>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                    {{ formatCurrency(clipData.total_views || 0) }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Valid Views</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ formatCurrency(clipData.valid_views) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reward</div>
                            <div class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                                Rp {{ formatCurrency(clipData.approved_reward || clipData.pending_reward) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Submitted</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ new Date(clipData.submitted_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Content URL -->
                    <div v-if="clipData.content_url" class="mb-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Content URL</div>
                        <a
                            :href="clipData.content_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 dark:text-blue-400 hover:underline break-all"
                        >
                            {{ clipData.content_url }}
                        </a>
                    </div>

                    <!-- Rejection Reason -->
                    <div v-if="clipData.status === 'rejected' && clipData.rejection_reason" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">Rejection Reason</div>
                        <div class="text-sm text-red-700 dark:text-red-300">
                            {{ clipData.rejection_reason }}
                        </div>
                    </div>

                    <!-- Transfer Status & Payment Info -->
                    <div v-if="clipData.status === 'approved' || clipData.status === 'paid'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Transfer Status:</span>
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full',
                                        clipData.status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                        clipData.status === 'approved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                    ]"
                                >
                                    {{ clipData.status === 'paid' ? 'Completed' : clipData.status === 'approved' ? 'Processing' : 'Pending' }}
                                </span>
                            </div>
                            <div v-if="clipData.paid_at" class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Paid At:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ new Date(clipData.paid_at).toLocaleString('id-ID') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Approved Reward:</span>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ formatCurrency(clipData.approved_reward || clipData.pending_reward) }}
                                </span>
                            </div>
                            <div v-if="clipData.status === 'paid'" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link
                                    :href="route('clipper.wallet.clipper')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    View Wallet
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Validation Status -->
                <ViewValidationStatus
                    v-if="validationData"
                    :validation-data="validationData"
                    :show-details="true"
                    :show-timeline="true"
                    :clip-id="clipData.id"
                />

                <!-- Fraud Detection Details -->
                <div v-if="validationData && validationData.fraud_detected" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <FraudDetectionDetails
                        :fraud-detected="validationData.fraud_detected"
                        :fraud-reasons="validationData.fraud_reasons || []"
                        :stability-score="validationData.stability_score"
                        :clip-id="clipData.id"
                    />
                </div>

                <!-- View History Chart -->
                <div v-if="viewTrackingData && viewTrackingData.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <ViewHistoryChart
                        :tracking-data="viewTrackingData.map(track => ({
                            tracked_at: track.tracked_at,
                            views_count: track.views_count,
                            is_valid: track.is_valid !== false,
                            stability_score: track.stability_score,
                        }))"
                        time-range="7d"
                        :show-valid-invalid="true"
                        :height="300"
                    />
                </div>

                <!-- Validation Timeline -->
                <div v-if="validationData && validationData.validation_history && validationData.validation_history.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <ValidationTimeline :validation-history="validationData.validation_history" />
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

