<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
    type: {
        type: String,
        default: 'views', // 'views', 'roi', 'spending'
        validator: (value) => ['views', 'roi', 'spending'].includes(value),
    },
    title: {
        type: String,
        default: null,
    },
});

const chartTitle = computed(() => {
    if (props.title) return props.title;
    
    const titles = {
        views: 'Views Over Time',
        roi: 'ROI Analysis',
        spending: 'Spending Breakdown',
    };
    return titles[props.type] || 'Chart';
});

const chartData = computed(() => {
    if (!props.data || Object.keys(props.data).length === 0) {
        return { labels: [], values: [] };
    }

    if (props.type === 'spending') {
        // For pie chart data
        return props.data;
    }

    // For line/bar chart data
    const labels = Object.keys(props.data).sort();
    const values = labels.map(label => props.data[label] || 0);

    return { labels, values };
});

const maxValue = computed(() => {
    if (props.type === 'spending') {
        const values = Object.values(props.data);
        return Math.max(...values, 1);
    }
    return Math.max(...chartData.value.values, 1);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
};

const getColor = (index, total) => {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-yellow-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-red-500',
        'bg-orange-500',
    ];
    return colors[index % colors.length];
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            {{ chartTitle }}
        </h3>
        <div class="h-64 flex items-center justify-center">
            <div v-if="!data || Object.keys(data).length === 0" class="text-center text-gray-500 dark:text-gray-400">
                <p>No data available</p>
                <p class="text-sm mt-2">Data will appear here as campaigns progress</p>
            </div>
            
            <!-- Views Chart (Line/Bar) -->
            <div v-else-if="type === 'views'" class="w-full">
                <div class="space-y-3">
                    <div
                        v-for="(value, index) in chartData.values"
                        :key="index"
                        class="flex items-center gap-4"
                    >
                        <div class="text-xs text-gray-500 dark:text-gray-400 w-20">
                            {{ formatDate(chartData.labels[index]) }}
                        </div>
                        <div class="flex-1 flex items-center">
                            <div
                                class="bg-blue-500 rounded h-8 flex items-center justify-end pr-2 text-white text-xs font-medium transition-all"
                                :style="{ width: `${Math.min((value / maxValue) * 100, 100)}%` }"
                            >
                                <span v-if="value > 0">{{ formatCurrency(value) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 mt-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Views</span>
                    </div>
                </div>
            </div>

            <!-- ROI Chart (Bar) -->
            <div v-else-if="type === 'roi'" class="w-full">
                <div class="h-48 flex items-end justify-between space-x-2">
                    <div
                        v-for="(value, index) in chartData.values"
                        :key="index"
                        class="flex-1 flex flex-col items-center"
                    >
                        <div
                            class="w-full bg-green-500 rounded-t transition-all"
                            :style="{ height: `${Math.min((value / maxValue) * 100, 100)}%` }"
                        ></div>
                        <p class="text-xs mt-2 text-center">{{ formatDate(chartData.labels[index]) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ value.toFixed(2) }}x</p>
                    </div>
                </div>
                <div class="flex gap-4 mt-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">ROI</span>
                    </div>
                </div>
            </div>

            <!-- Spending Chart (Pie/Bar) -->
            <div v-else-if="type === 'spending'" class="w-full">
                <div class="space-y-3">
                    <div
                        v-for="(value, label, index) in chartData"
                        :key="label"
                        class="flex items-center gap-4"
                    >
                        <div class="text-xs text-gray-500 dark:text-gray-400 w-32 capitalize">
                            {{ label }}
                        </div>
                        <div class="flex-1 flex items-center">
                            <div
                                :class="[getColor(index, Object.keys(chartData).length), 'rounded h-8 flex items-center justify-end pr-2 text-white text-xs font-medium transition-all']"
                                :style="{ width: `${Math.min((value / maxValue) * 100, 100)}%` }"
                            >
                                <span v-if="value > 0">Rp {{ formatCurrency(value) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4 mt-4 text-sm">
                    <div
                        v-for="(value, label, index) in chartData"
                        :key="label"
                        class="flex items-center gap-2"
                    >
                        <div :class="[getColor(index, Object.keys(chartData).length), 'w-4 h-4 rounded']"></div>
                        <span class="text-gray-600 dark:text-gray-400 capitalize">{{ label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

