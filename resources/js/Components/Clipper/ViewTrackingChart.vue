<script setup>
import { computed } from 'vue';

const props = defineProps({
    trackingData: {
        type: Array,
        default: () => [],
    },
});

const chartData = computed(() => {
    if (!props.trackingData || props.trackingData.length === 0) {
        return [];
    }

    return props.trackingData
        .sort((a, b) => new Date(a.tracked_at) - new Date(b.tracked_at))
        .map(item => ({
            date: new Date(item.tracked_at).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }),
            views: item.views_count || 0,
            isValid: item.is_valid !== false,
        }));
});

const maxViews = computed(() => {
    if (chartData.value.length === 0) return 1;
    return Math.max(...chartData.value.map(d => d.views), 1);
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            View Tracking Over Time
        </h3>
        <div class="h-64 flex items-center justify-center">
            <div v-if="!trackingData || trackingData.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                <p>No tracking data available</p>
                <p class="text-sm mt-2">Views will be tracked automatically after submission</p>
            </div>
            <div v-else class="w-full">
                <!-- Bar chart representation -->
                <div class="space-y-3">
                    <div
                        v-for="(item, index) in chartData"
                        :key="index"
                        class="flex items-center gap-4"
                    >
                        <div class="text-xs text-gray-500 dark:text-gray-400 w-20">
                            {{ item.date }}
                        </div>
                        <div class="flex-1 flex items-center gap-2">
                            <div
                                :class="[
                                    'rounded h-8 flex items-center justify-end pr-2 text-white text-xs font-medium transition-all',
                                    item.isValid ? 'bg-blue-500' : 'bg-red-500'
                                ]"
                                :style="{ width: `${Math.min((item.views / maxViews) * 100, 100)}%` }"
                            >
                                <span v-if="item.views > 0">{{ formatNumber(item.views) }}</span>
                            </div>
                            <div v-if="!item.isValid" class="text-xs text-red-500" title="Invalid views">
                                ⚠️
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 mt-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Valid Views</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Invalid Views</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

