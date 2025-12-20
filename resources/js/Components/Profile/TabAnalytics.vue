<script setup>
import StatsOverview from '@/Components/Analytics/StatsOverview.vue';
import EngagementChart from '@/Components/Analytics/EngagementChart.vue';
import TopPosts from '@/Components/Analytics/TopPosts.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    engagement_data: {
        type: Object,
        default: () => ({}),
    },
    top_posts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="space-y-6">
        <StatsOverview :stats="stats" />
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <EngagementChart :data="engagement_data" />
            <TopPosts :posts="top_posts" />
        </div>
    </div>
</template>

