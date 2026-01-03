<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    userGrowthTrends: {
        type: Object,
        default: () => ({ labels: [], data: [] }),
    },
    salesTrends: {
        type: Object,
        default: () => ({ labels: [], data: [] }),
    },
    postTrends: {
        type: Object,
        default: () => ({ labels: [], data: [] }),
    },
    period: {
        type: String,
        default: 'monthly',
    },
});

const userGrowthCanvas = ref(null);
const salesCanvas = ref(null);
const postCanvas = ref(null);

const renderChart = (canvas, labels, data, label, color = '#3B82F6') => {
    if (!canvas || !labels || labels.length === 0) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const container = canvas.parentElement;
    if (!container) return;

    const rect = container.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = rect.width;
    const height = 200;

    canvas.width = width * dpr;
    canvas.height = height * dpr;
    canvas.style.width = width + 'px';
    canvas.style.height = height + 'px';
    ctx.scale(dpr, dpr);

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    const padding = { top: 20, right: 20, bottom: 40, left: 50 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    const maxValue = Math.max(...data, 1);

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
    const labelStep = Math.max(1, Math.floor(labels.length / 8));
    labels.forEach((label, index) => {
        if (index % labelStep === 0 || index === labels.length - 1) {
            const x = padding.left + (chartWidth / (labels.length - 1 || 1)) * index;
            ctx.save();
            ctx.translate(x, height - padding.bottom + 5);
            ctx.rotate(-Math.PI / 4);
            ctx.fillText(label, 0, 0);
            ctx.restore();
        }
    });

    // Draw line
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.beginPath();

    data.forEach((value, index) => {
        const x = padding.left + (chartWidth / (labels.length - 1 || 1)) * index;
        const y = height - padding.bottom - (value / maxValue) * chartHeight;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    ctx.stroke();

    // Draw points
    ctx.fillStyle = color;
    data.forEach((value, index) => {
        const x = padding.left + (chartWidth / (labels.length - 1 || 1)) * index;
        const y = height - padding.bottom - (value / maxValue) * chartHeight;
        
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
    });
};

const resizeChart = (canvas) => {
    if (canvas) {
        const container = canvas.parentElement;
        if (container) {
            const rect = container.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.style.width = rect.width + 'px';
        }
    }
};

onMounted(() => {
    if (userGrowthCanvas.value && props.userGrowthTrends.labels.length > 0) {
        renderChart(
            userGrowthCanvas.value,
            props.userGrowthTrends.labels,
            props.userGrowthTrends.data,
            'User Growth',
            '#3B82F6'
        );
    }
    if (salesCanvas.value && props.salesTrends.labels.length > 0) {
        renderChart(
            salesCanvas.value,
            props.salesTrends.labels,
            props.salesTrends.data,
            'Sales',
            '#10B981'
        );
    }
    if (postCanvas.value && props.postTrends.labels.length > 0) {
        renderChart(
            postCanvas.value,
            props.postTrends.labels,
            props.postTrends.data,
            'Posts',
            '#8B5CF6'
        );
    }

    window.addEventListener('resize', () => {
        resizeChart(userGrowthCanvas.value);
        resizeChart(salesCanvas.value);
        resizeChart(postCanvas.value);
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', () => {});
});

watch(() => [props.userGrowthTrends, props.salesTrends, props.postTrends], () => {
    if (userGrowthCanvas.value && props.userGrowthTrends.labels.length > 0) {
        renderChart(
            userGrowthCanvas.value,
            props.userGrowthTrends.labels,
            props.userGrowthTrends.data,
            'User Growth',
            '#3B82F6'
        );
    }
    if (salesCanvas.value && props.salesTrends.labels.length > 0) {
        renderChart(
            salesCanvas.value,
            props.salesTrends.labels,
            props.salesTrends.data,
            'Sales',
            '#10B981'
        );
    }
    if (postCanvas.value && props.postTrends.labels.length > 0) {
        renderChart(
            postCanvas.value,
            props.postTrends.labels,
            props.postTrends.data,
            'Posts',
            '#8B5CF6'
        );
    }
}, { deep: true });
</script>

<template>
    <div class="analytics-charts space-y-6">
        <!-- User Growth Chart -->
        <div v-if="userGrowthTrends.labels.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">User Growth</h3>
            <div class="relative h-48">
                <canvas ref="userGrowthCanvas" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Sales Chart -->
        <div v-if="salesTrends.labels.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Sales Trends</h3>
            <div class="relative h-48">
                <canvas ref="salesCanvas" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Post Creation Chart -->
        <div v-if="postTrends.labels.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Post Creation Trends</h3>
            <div class="relative h-48">
                <canvas ref="postCanvas" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
</template>

