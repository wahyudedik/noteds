<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import RepostAnalytics from '@/Components/Analytics/RepostAnalytics.vue';

const props = defineProps({
    post: Object,
    breakdown: Object,
    engagement: Object,
    timeline: Array,
    reposters: Array,
});
</script>

<template>
    <Head :title="'Repost Analytics - ' + post.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Repost Analytics
                </h2>
                <Link
                    :href="route('posts.show', post.id)"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    ← Back to Post
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <RepostAnalytics
                    :post="post"
                    :breakdown="breakdown"
                    :engagement="engagement"
                    :timeline="timeline"
                    :reposters="reposters"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

