<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    timeline: {
        type: Array,
        default: () => [],
    },
    postId: {
        type: String,
        default: null,
    },
});

const dateRange = ref('30');

const chartData = computed(() => {
    return props.timeline.map(entry => ({
        date: entry.date,
        reposts: entry.reposts_count,
        quote: entry.quote_reposts_count,
        withComments: entry.reposts_with_comments_count,
    }));
});

const updateDateRange = () => {
    if (props.postId) {
        router.get(route('reposts.timeline', props.postId), {
            days: dateRange.value,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Date Range Selector -->
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium">Date Range:</label>
            <select
                v-model="dateRange"
                @change="updateDateRange"
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300 text-sm"
            >
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
            </select>
        </div>

        <!-- Timeline Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Reposts Over Time</h3>
            <div class="space-y-2">
                <div
                    v-for="entry in chartData"
                    :key="entry.date"
                    class="flex items-center gap-4"
                >
                    <div class="w-24 text-xs text-gray-500">
                        {{ entry.date }}
                    </div>
                    <div class="flex-1 flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-6 relative">
                            <div
                                class="bg-indigo-600 h-6 rounded-full flex items-center justify-center text-xs text-white"
                                :style="{ width: `${Math.min(100, (entry.reposts / Math.max(...chartData.map(e => e.reposts), 1)) * 100)}%` }"
                            >
                                <span v-if="entry.reposts > 0">{{ entry.reposts }}</span>
                            </div>
                        </div>
                        <div class="w-20 text-xs text-gray-500 text-right">
                            Quote: {{ entry.quote }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

