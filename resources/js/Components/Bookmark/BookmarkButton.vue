<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
    isBookmarked: {
        type: Boolean,
        default: false,
    },
    canBookmark: {
        type: Boolean,
        default: true,
    },
});

const toggleBookmark = () => {
    if (!props.canBookmark) return;

    if (props.isBookmarked) {
        router.delete(route('posts.unbookmark', props.postId), {
            preserveScroll: true,
            preserveState: true,
        });
    } else {
        router.post(route('posts.bookmark', props.postId), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <button
        @click="toggleBookmark"
        :disabled="!canBookmark"
        :class="[
            'flex items-center gap-1 px-3 py-1 rounded-md text-sm transition',
            isBookmarked
                ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
            !canBookmark && 'opacity-50 cursor-not-allowed'
        ]"
        :title="isBookmarked ? 'Remove bookmark' : 'Bookmark this post'"
    >
        <svg
            v-if="isBookmarked"
            class="w-4 h-4"
            fill="currentColor"
            viewBox="0 0 20 20"
        >
            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
        </svg>
        <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
        <span>{{ isBookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
    </button>
</template>

