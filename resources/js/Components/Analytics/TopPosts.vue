<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Top Performing Posts
        </h3>
        <div v-if="posts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No posts yet</p>
            <p class="text-sm mt-2">Create your first post to see analytics!</p>
        </div>
        <div v-else class="space-y-4">
            <Link
                v-for="(post, index) in posts"
                :key="post.id"
                :href="route('posts.show', post.id)"
                class="block p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                #{{ index + 1 }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ new Date(post.created_at).toLocaleDateString('id-ID') }}
                            </span>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1 line-clamp-2">
                            {{ post.title }}
                        </h4>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        👍 {{ post.upvotes_count }}
                    </span>
                    <span class="flex items-center gap-1">
                        👎 {{ post.downvotes_count }}
                    </span>
                    <span class="flex items-center gap-1">
                        💬 {{ post.comments_count }}
                    </span>
                </div>
            </Link>
        </div>
    </div>
</template>

