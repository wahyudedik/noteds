<script setup>
import PostCard from '@/Components/PostCard.vue';

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
});
</script>

<template>
    <div>
        <div v-if="posts.length === 0" class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">
                No posts yet. Start sharing your business ideas!
            </p>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <PostCard
                v-for="post in posts"
                :key="post.id"
                :post="post"
                :user-vote="userVotes && userVotes[post.id] ? userVotes[post.id] : null"
                :is-bookmarked="userBookmarks && userBookmarks[post.id] ? userBookmarks[post.id] : false"
            />
        </div>
    </div>
</template>

