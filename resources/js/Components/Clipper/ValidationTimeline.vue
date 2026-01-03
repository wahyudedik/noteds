<script setup>
import { computed } from 'vue';

const props = defineProps({
    validationHistory: {
        type: Array,
        default: () => [],
    },
});

const sortedHistory = computed(() => {
    return [...props.validationHistory].sort((a, b) => 
        new Date(a.tracked_at) - new Date(b.tracked_at)
    );
});

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getEventType = (entry) => {
    if (!entry.is_valid) {
        return 'invalid';
    }
    if (entry.stability_score !== null && entry.stability_score > 0.8) {
        return 'unstable';
    }
    return 'valid';
};

const getEventColor = (type) => {
    const colors = {
        valid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-300 dark:border-green-700',
        invalid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-red-300 dark:border-red-700',
        unstable: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700',
    };
    return colors[type] || colors.valid;
};

const getEventIcon = (type) => {
    if (type === 'invalid') {
        return '❌';
    }
    if (type === 'unstable') {
        return '⚠️';
    }
    return '✅';
};
</script>

<template>
    <div class="validation-timeline">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Validation Timeline</h3>
        
        <div v-if="sortedHistory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            No validation history available
        </div>

        <div v-else class="relative">
            <!-- Timeline line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <!-- Timeline events -->
            <div class="space-y-6">
                <div
                    v-for="(entry, index) in sortedHistory"
                    :key="index"
                    class="relative flex items-start gap-4"
                >
                    <!-- Timeline dot -->
                    <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600">
                        <div
                            :class="[
                                'w-3 h-3 rounded-full',
                                entry.is_valid ? 'bg-green-500' : 'bg-red-500'
                            ]"
                        ></div>
                    </div>

                    <!-- Event content -->
                    <div class="flex-1 pb-6">
                        <div
                            :class="[
                                'border rounded-lg p-4',
                                getEventColor(getEventType(entry))
                            ]"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ getEventIcon(getEventType(entry)) }}</span>
                                    <span class="font-semibold">
                                        {{ entry.is_valid ? 'Valid View Tracking' : 'Invalid View Detected' }}
                                    </span>
                                </div>
                                <span class="text-sm opacity-75">
                                    {{ formatDateTime(entry.tracked_at) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-3">
                                <div>
                                    <div class="text-xs opacity-75 mb-1">Views Count</div>
                                    <div class="font-bold text-lg">
                                        {{ formatCurrency(entry.views_count) }}
                                    </div>
                                </div>
                                <div v-if="entry.stability_score !== null">
                                    <div class="text-xs opacity-75 mb-1">Stability Score</div>
                                    <div class="font-bold text-lg">
                                        {{ entry.stability_score.toFixed(2) }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="!entry.is_valid" class="mt-3 pt-3 border-t border-current border-opacity-20">
                                <div class="text-sm font-medium">
                                    This view tracking was marked as invalid due to suspicious patterns.
                                </div>
                            </div>

                            <div v-if="entry.stability_score !== null && entry.stability_score > 0.6" class="mt-3 pt-3 border-t border-current border-opacity-20">
                                <div class="text-sm font-medium">
                                    High instability detected. View growth pattern shows significant fluctuations.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

