<script setup>
import { ref, computed, watch } from 'vue';
import { useRealTimePolling } from '@/Composables/useRealTimePolling';

const props = defineProps({
    initialViews: {
        type: Number,
        default: 0,
    },
    endpoint: {
        type: String,
        required: true,
    },
    pollInterval: {
        type: Number,
        default: 30000, // 30 seconds
    },
    showGrowthRate: {
        type: Boolean,
        default: true,
    },
    enabled: {
        type: Boolean,
        default: true,
    },
});

const previousViews = ref(props.initialViews);
const animatedViews = ref(props.initialViews);

const { data, loading, error, isPolling, lastUpdated } = useRealTimePolling(
    props.endpoint,
    {
        interval: props.pollInterval,
        enabled: props.enabled,
        transform: (responseData) => {
            // Extract views from response (handle different response formats)
            if (responseData.total_views !== undefined) {
                return {
                    total_views: responseData.total_views,
                    valid_views: responseData.valid_views || 0,
                    invalid_views: responseData.invalid_views || 0,
                    growth_rate: responseData.growth_rate || 0,
                    last_updated: responseData.last_updated,
                };
            } else if (responseData.current_views !== undefined) {
                return {
                    total_views: responseData.current_views,
                    valid_views: responseData.valid_views || 0,
                    growth_rate: responseData.growth_rate || 0,
                    last_updated: responseData.last_tracking_timestamp,
                };
            }
            return responseData;
        },
    }
);

// Animate number changes
watch(() => data.value?.total_views, (newViews) => {
    if (newViews !== undefined && newViews !== null) {
        const start = animatedViews.value;
        const end = newViews;
        const duration = 500; // 500ms animation
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            
            animatedViews.value = Math.floor(start + (end - start) * easeOut);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                animatedViews.value = end;
                previousViews.value = end;
            }
        };

        requestAnimationFrame(animate);
    }
}, { immediate: true });

const growthRate = computed(() => {
    return data.value?.growth_rate || 0;
});

const growthIndicator = computed(() => {
    if (growthRate.value > 0) return 'up';
    if (growthRate.value < 0) return 'down';
    return 'stable';
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit',
    });
};
</script>

<template>
    <div class="real-time-view-counter">
        <div class="flex items-center gap-2 mb-2">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Views</div>
            <span
                v-if="isPolling"
                class="px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded"
                title="Auto-refreshing every 30 seconds"
            >
                🔄 Live
            </span>
            <span
                v-if="loading"
                class="px-2 py-0.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded"
            >
                Loading...
            </span>
        </div>
        
        <div class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300">
            {{ formatCurrency(animatedViews) }}
        </div>

        <div v-if="showGrowthRate && growthRate !== 0" class="flex items-center gap-2 mt-2">
            <svg
                v-if="growthIndicator === 'up'"
                class="w-4 h-4 text-green-600 dark:text-green-400"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            <svg
                v-else-if="growthIndicator === 'down'"
                class="w-4 h-4 text-red-600 dark:text-red-400"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span
                :class="[
                    'text-sm font-semibold',
                    growthIndicator === 'up' ? 'text-green-600 dark:text-green-400' :
                    growthIndicator === 'down' ? 'text-red-600 dark:text-red-400' :
                    'text-gray-600 dark:text-gray-400'
                ]"
            >
                {{ Math.abs(growthRate).toFixed(1) }}%
            </span>
        </div>

        <div v-if="lastUpdated || data?.last_updated" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Updated: {{ formatTime(lastUpdated || data?.last_updated) }}
        </div>

        <div v-if="error" class="mt-2 text-xs text-red-600 dark:text-red-400">
            Error: {{ error.message }}
        </div>
    </div>
</template>

