<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    seriesNavigation: {
        type: Object,
        default: null,
    },
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <div v-if="seriesNavigation && seriesNavigation.total_posts > 1" class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Series: {{ seriesNavigation.series_root?.title }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Part {{ seriesNavigation.current_index }} of {{ seriesNavigation.total_posts }}
                </p>
            </div>
            <Link
                :href="route('posts.show', seriesNavigation.series_root.id)"
                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
            >
                View Series
            </Link>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${(seriesNavigation.current_index / seriesNavigation.total_posts) * 100}%` }"
                ></div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between gap-2">
            <Link
                v-if="seriesNavigation.previous_post"
                :href="route('posts.show', seriesNavigation.previous_post.id)"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                ← Previous
            </Link>
            <div v-else class="flex-1 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md cursor-not-allowed">
                ← Previous
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 px-2">
                {{ seriesNavigation.current_index }} / {{ seriesNavigation.total_posts }}
            </div>

            <Link
                v-if="seriesNavigation.next_post"
                :href="route('posts.show', seriesNavigation.next_post.id)"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition text-right"
            >
                Next →
            </Link>
            <div v-else class="flex-1 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md cursor-not-allowed text-right">
                Next →
            </div>
        </div>

        <!-- Series Posts List -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">All Posts in Series:</p>
            <div class="space-y-1">
                <Link
                    v-for="(post, index) in seriesNavigation.all_posts"
                    :key="post.id"
                    :href="route('posts.show', post.id)"
                    :class="[
                        'block px-3 py-2 text-xs rounded-md transition',
                        post.id === seriesNavigation.current_post.id
                            ? 'bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 font-medium'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                >
                    <span class="font-mono text-gray-400 dark:text-gray-500 mr-2">{{ index + 1 }}.</span>
                    {{ post.title }}
                </Link>
            </div>
        </div>
    </div>
</template>

