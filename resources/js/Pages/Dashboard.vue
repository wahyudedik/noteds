<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsOverview from '@/Components/Analytics/StatsOverview.vue';
import EngagementChart from '@/Components/Analytics/EngagementChart.vue';
import TopPosts from '@/Components/Analytics/TopPosts.vue';
import ActivityTimeline from '@/Components/Analytics/ActivityTimeline.vue';
import { Head } from '@inertiajs/vue3';

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
    recent_activities: {
        type: Array,
        default: () => [],
    },
    purpose_type_stats: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Dashboard - Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Analytics Dashboard
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Stats Overview -->
                <StatsOverview :stats="stats" />

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <EngagementChart :data="engagement_data" />
                    <TopPosts :posts="top_posts" />
                </div>

                <!-- Activity Timeline -->
                <ActivityTimeline :activities="recent_activities" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
