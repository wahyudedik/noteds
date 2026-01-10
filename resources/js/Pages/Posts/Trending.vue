<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import TrendingBadge from '@/Components/TrendingBadge.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    userVotes: {
        type: Object,
        default: () => ({}),
    },
    userBookmarks: {
        type: Object,
        default: () => ({}),
    },
    userReposts: {
        type: Object,
        default: () => ({}),
    },
    userPollVotes: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Trending Posts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Trending Posts
                    </h2>
                    <TrendingBadge />
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Trending posts</strong> are determined by engagement (votes, comments, reposts) and recency. 
                        Posts are ranked by their trending score, which favors recent posts with high engagement.
                    </p>
                </div>

                <div v-if="posts.length > 0" class="space-y-4">
                    <PostCard
                        v-for="post in posts"
                        :key="post.id"
                        :post="post"
                        :user-vote="userVotes[post.id]"
                        :is-bookmarked="userBookmarks[post.id]"
                        :is-reposted="userReposts[post.id]"
                        :user-poll-vote="userPollVotes[post.id]"
                    />
                </div>

                <div v-else class="text-center py-12">
                    <svg
                        class="mx-auto h-16 w-16 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                        />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                        No trending posts yet
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Trending posts will appear here once there's enough engagement.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


