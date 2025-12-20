<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
});

const chartData = computed(() => {
    if (!props.data || Object.keys(props.data).length === 0) {
        return { labels: [], datasets: [] };
    }

    const labels = Object.keys(props.data).sort();
    const upvotes = labels.map(date => props.data[date].upvotes || 0);
    const comments = labels.map(date => props.data[date].comments || 0);

    return {
        labels,
        datasets: [
            {
                label: 'Upvotes',
                data: upvotes,
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
            },
            {
                label: 'Comments',
                data: comments,
                borderColor: 'rgb(139, 92, 246)',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
            },
        ],
    };
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Engagement Over Time
        </h3>
        <div class="h-64 flex items-center justify-center">
            <div v-if="!data || Object.keys(data).length === 0" class="text-center text-gray-500 dark:text-gray-400">
                <p>No engagement data available</p>
                <p class="text-sm mt-2">Start posting to see your engagement metrics!</p>
            </div>
            <div v-else class="w-full">
                <!-- Simple bar chart representation -->
                <div class="space-y-2">
                    <div
                        v-for="(value, date) in data"
                        :key="date"
                        class="flex items-center gap-4"
                    >
                        <div class="text-xs text-gray-500 dark:text-gray-400 w-20">
                            {{ new Date(date).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' }) }}
                        </div>
                        <div class="flex-1 flex gap-2">
                            <div
                                class="bg-indigo-500 rounded h-6 flex items-center justify-end pr-2 text-white text-xs"
                                :style="{ width: `${Math.min((value.upvotes || 0) * 10, 100)}%` }"
                            >
                                <span v-if="value.upvotes > 0">{{ value.upvotes }}</span>
                            </div>
                            <div
                                class="bg-purple-500 rounded h-6 flex items-center justify-end pr-2 text-white text-xs"
                                :style="{ width: `${Math.min((value.comments || 0) * 10, 100)}%` }"
                            >
                                <span v-if="value.comments > 0">{{ value.comments }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 mt-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-indigo-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Upvotes</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-purple-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Comments</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

