<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    clip: Object,
    viewTracking: {
        type: Array,
        default: () => [],
    },
});

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

const chartCanvas = ref(null);

onMounted(() => {
    if (props.viewTracking && props.viewTracking.length > 0 && chartCanvas.value) {
        renderChart();
    }
});

const renderChart = () => {
    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx || !props.viewTracking || props.viewTracking.length === 0) return;

    const data = props.viewTracking;
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

    <AuthenticatedLayout>
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
                                    Clip #{{ clip.id.substring(0, 8) }}
                                </h1>
                                <span
                                    :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(clip.status)]"
                                >
                                    {{ clip.status }}
                                </span>
                            </div>

                            <div v-if="clip.campaign" class="mb-4">
                                <Link
                                    :href="route('clipper.campaigns.show', clip.campaign.id)"
                                    class="text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Campaign: {{ clip.campaign.title }}
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
                            <div class="text-sm text-gray-500 dark:text-gray-400">Valid Views</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ formatCurrency(clip.valid_views) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reward</div>
                            <div class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                                Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Submitted</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ new Date(clip.submitted_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Content URL -->
                    <div v-if="clip.content_url" class="mb-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Content URL</div>
                        <a
                            :href="clip.content_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 dark:text-blue-400 hover:underline break-all"
                        >
                            {{ clip.content_url }}
                        </a>
                    </div>

                    <!-- Rejection Reason -->
                    <div v-if="clip.status === 'rejected' && clip.rejection_reason" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">Rejection Reason</div>
                        <div class="text-sm text-red-700 dark:text-red-300">
                            {{ clip.rejection_reason }}
                        </div>
                    </div>
                </div>

                <!-- View Tracking Chart -->
                <div v-if="viewTracking && viewTracking.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">View Tracking</h3>
                    <div class="h-64">
                        <canvas ref="chartCanvas" width="800" height="200"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div
                            v-for="track in viewTracking"
                            :key="track.id"
                            class="flex justify-between items-center text-sm"
                        >
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ new Date(track.tracked_at).toLocaleString() }}
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(track.views_count) }} views
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

