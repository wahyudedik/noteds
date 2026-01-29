<script setup>
import RateLimitChart from '@/Components/Admin/RateLimitChart.vue';
const props = defineProps({
    userGrowthTrends: { type: Object, default: () => ({ labels: [], data: [] }) },
    salesTrends: { type: Object, default: () => ({ labels: [], data: [] }) },
    postTrends: { type: Object, default: () => ({ labels: [], data: [] }) },
    period: { type: String, default: 'monthly' },
    chartTypeUser: { type: String, default: 'line' },
    chartTypeSales: { type: String, default: 'bar' },
    chartTypePost: { type: String, default: 'line' },
});
</script>

<template>
    <div class="analytics-charts space-y-6">
        <RateLimitChart v-if="userGrowthTrends.labels.length > 0" title="User Growth" :labels="userGrowthTrends.labels" :data="userGrowthTrends.data" :type="chartTypeUser" color="#3B82F6" />

        <RateLimitChart v-if="salesTrends.labels.length > 0" title="Sales Trends" :labels="salesTrends.labels" :data="salesTrends.data" :type="chartTypeSales" color="#10B981" />

        <RateLimitChart v-if="postTrends.labels.length > 0" title="Post Creation Trends" :labels="postTrends.labels" :data="postTrends.data" :type="chartTypePost" color="#8B5CF6" />
    </div>
</template>

