<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';

const emit = defineEmits(['update:timeRange']);

const props = defineProps({
    trackingData: {
        type: Array,
        default: () => [],
    },
    timeRange: {
        type: String,
        default: '7d', // '24h', '7d', '30d', 'all'
    },
    showValidInvalid: {
        type: Boolean,
        default: true,
    },
    height: {
        type: Number,
        default: 300,
    },
});

const timeRange = ref(props.timeRange);

watch(() => props.timeRange, (newValue) => {
    timeRange.value = newValue;
});

const chartCanvas = ref(null);
let chartInstance = null;

const filteredData = computed(() => {
    if (!props.trackingData || props.trackingData.length === 0) {
        return [];
    }

    const now = new Date();
    let cutoffDate = new Date();

    switch (props.timeRange) {
        case '24h':
            cutoffDate.setHours(now.getHours() - 24);
            break;
        case '7d':
            cutoffDate.setDate(now.getDate() - 7);
            break;
        case '30d':
            cutoffDate.setDate(now.getDate() - 30);
            break;
        case 'all':
        default:
            return props.trackingData;
    }

    return props.trackingData.filter(item => {
        const itemDate = new Date(item.tracked_at);
        return itemDate >= cutoffDate;
    });
});

const chartData = computed(() => {
    const data = filteredData.value;
    
    if (data.length === 0) {
        return {
            labels: [],
            validViews: [],
            invalidViews: [],
            totalViews: [],
        };
    }

    // Sort by date
    const sorted = [...data].sort((a, b) => 
        new Date(a.tracked_at) - new Date(b.tracked_at)
    );

    return {
        labels: sorted.map(item => {
            const date = new Date(item.tracked_at);
            if (props.timeRange === '24h') {
                return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
        }),
        validViews: sorted.map(item => item.is_valid ? item.views_count : 0),
        invalidViews: sorted.map(item => item.is_valid ? 0 : item.views_count),
        totalViews: sorted.map(item => item.views_count),
    };
});

const renderChart = () => {
    if (!chartCanvas.value) return;

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) return;

    const container = chartCanvas.value.parentElement;
    if (!container) return;

    const rect = container.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = rect.width;
    const height = props.height;

    // Set canvas size
    chartCanvas.value.width = width * dpr;
    chartCanvas.value.height = height * dpr;
    chartCanvas.value.style.width = width + 'px';
    chartCanvas.value.style.height = height + 'px';
    ctx.scale(dpr, dpr);

    const data = chartData.value;
    
    if (data.labels.length === 0) {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '14px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('No data available', width / 2, height / 2);
        return;
    }

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    const padding = { top: 20, right: 20, bottom: 40, left: 50 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    const maxValue = Math.max(
        ...data.totalViews,
        ...data.validViews,
        ...data.invalidViews,
        1
    );

    // Draw axes
    ctx.strokeStyle = '#9CA3AF';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding.left, padding.top);
    ctx.lineTo(padding.left, height - padding.bottom);
    ctx.lineTo(width - padding.right, height - padding.bottom);
    ctx.stroke();

    // Draw grid lines
    ctx.strokeStyle = '#E5E7EB';
    ctx.lineWidth = 0.5;
    const gridLines = 5;
    for (let i = 0; i <= gridLines; i++) {
        const y = padding.top + (chartHeight / gridLines) * i;
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();
    }

    // Draw labels
    ctx.fillStyle = '#6B7280';
    ctx.font = '12px sans-serif';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    
    // Y-axis labels
    for (let i = 0; i <= gridLines; i++) {
        const value = maxValue - (maxValue / gridLines) * i;
        const y = padding.top + (chartHeight / gridLines) * i;
        ctx.fillText(Math.floor(value).toLocaleString('id-ID'), padding.left - 10, y);
    }

    // X-axis labels
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    const labelStep = Math.max(1, Math.floor(data.labels.length / 8));
    data.labels.forEach((label, index) => {
        if (index % labelStep === 0 || index === data.labels.length - 1) {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * index;
            ctx.save();
            ctx.translate(x, height - padding.bottom + 5);
            ctx.rotate(-Math.PI / 4);
            ctx.fillText(label, 0, 0);
            ctx.restore();
        }
    });

    if (props.showValidInvalid && data.validViews.length > 0) {
        // Draw stacked area chart for valid/invalid views
        // Invalid views (red) at bottom
        ctx.fillStyle = 'rgba(239, 68, 68, 0.6)';
        ctx.beginPath();
        ctx.moveTo(padding.left, height - padding.bottom);
        
        data.invalidViews.forEach((value, index) => {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * index;
            const y = height - padding.bottom - (value / maxValue) * chartHeight;
            if (index === 0) {
                ctx.lineTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        // Complete the area
        for (let i = data.invalidViews.length - 1; i >= 0; i--) {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * i;
            const y = height - padding.bottom;
            ctx.lineTo(x, y);
        }
        ctx.closePath();
        ctx.fill();

        // Valid views (green) on top
        ctx.fillStyle = 'rgba(34, 197, 94, 0.6)';
        ctx.beginPath();
        
        data.validViews.forEach((value, index) => {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * index;
            const invalidY = height - padding.bottom - (data.invalidViews[index] / maxValue) * chartHeight;
            const y = invalidY - (value / maxValue) * chartHeight;
            if (index === 0) {
                ctx.moveTo(x, invalidY);
                ctx.lineTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        // Complete the area
        for (let i = data.validViews.length - 1; i >= 0; i--) {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * i;
            const invalidY = height - padding.bottom - (data.invalidViews[i] / maxValue) * chartHeight;
            ctx.lineTo(x, invalidY);
        }
        ctx.closePath();
        ctx.fill();
    } else {
        // Draw simple line chart for total views
        ctx.strokeStyle = '#3B82F6';
        ctx.lineWidth = 2;
        ctx.beginPath();

        data.totalViews.forEach((value, index) => {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * index;
            const y = height - padding.bottom - (value / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.stroke();

        // Draw points
        ctx.fillStyle = '#3B82F6';
        data.totalViews.forEach((value, index) => {
            const x = padding.left + (chartWidth / (data.labels.length - 1 || 1)) * index;
            const y = height - padding.bottom - (value / maxValue) * chartHeight;
            
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
};

const resizeChart = () => {
    if (chartCanvas.value) {
        renderChart();
    }
};

onMounted(() => {
    renderChart();
    window.addEventListener('resize', resizeChart);
});

onUnmounted(() => {
    window.removeEventListener('resize', resizeChart);
});

watch(() => [props.trackingData, props.timeRange], () => {
    renderChart();
}, { deep: true });
</script>

<template>
    <div class="view-history-chart">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">View History</h3>
            <div class="flex gap-2">
                <button
                    v-for="range in ['24h', '7d', '30d', 'all']"
                    :key="range"
                    @click="timeRange = range; emit('update:timeRange', range)"
                    :class="[
                        'px-3 py-1 text-sm rounded-lg transition-colors',
                        timeRange === range
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                >
                    {{ range === '24h' ? '24 Hours' : range === '7d' ? '7 Days' : range === '30d' ? '30 Days' : 'All Time' }}
                </button>
            </div>
        </div>

        <div class="relative" :style="{ height: height + 'px' }">
            <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        </div>

        <div v-if="showValidInvalid" class="flex gap-4 mt-4 justify-center">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Valid Views</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Invalid Views</span>
            </div>
        </div>
    </div>
</template>

