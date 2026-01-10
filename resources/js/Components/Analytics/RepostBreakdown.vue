<script setup>
import { computed } from 'vue';

const props = defineProps({
    breakdown: {
        type: Object,
        required: true,
    },
});

const chartData = computed(() => {
    return [
        { label: 'Regular', value: props.breakdown.regular, color: '#3B82F6' },
        { label: 'Quote', value: props.breakdown.quote, color: '#10B981' },
        { label: 'With Comments', value: props.breakdown.with_comments, color: '#F59E0B' },
    ].filter(item => item.value > 0);
});

const total = computed(() => props.breakdown.total || 0);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Repost Breakdown</h3>
        
        <div v-if="total > 0" class="space-y-4">
            <!-- Pie Chart Representation -->
            <div class="flex items-center justify-center">
                <div class="relative w-48 h-48">
                    <svg class="transform -rotate-90" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            stroke="#E5E7EB"
                            stroke-width="20"
                        />
                        <circle
                            v-for="(item, index) in chartData"
                            :key="index"
                            cx="50"
                            cy="50"
                            r="40"
                            fill="none"
                            :stroke="item.color"
                            stroke-width="20"
                            :stroke-dasharray="`${(item.value / total) * 251.2} 251.2`"
                            :stroke-dashoffset="getOffset(index)"
                            class="transition-all"
                        />
                    </svg>
                </div>
            </div>

            <!-- Legend -->
            <div class="space-y-2">
                <div
                    v-for="item in chartData"
                    :key="item.label"
                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="w-4 h-4 rounded"
                            :style="{ backgroundColor: item.color }"
                        ></div>
                        <span class="text-sm font-medium">{{ item.label }}</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ item.value }} ({{ props.breakdown.percentages[item.label.toLowerCase().replace(' ', '_')] || 0 }}%)
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
            No reposts yet.
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        getOffset(index) {
            let offset = 0;
            for (let i = 0; i < index; i++) {
                offset += (this.chartData[i].value / this.total) * 251.2;
            }
            return offset;
        },
    },
};
</script>

