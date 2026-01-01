<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

// Local state for optimistic update
const localIsBookmarked = ref(props.isBookmarked);

// Watch prop changes
watch(() => props.isBookmarked, (newVal) => {
    localIsBookmarked.value = newVal;
});

const toggleBookmark = () => {
    if (!props.canBookmark) return;

    // Optimistic update - immediately change UI
    const wasBookmarked = localIsBookmarked.value;
    localIsBookmarked.value = !wasBookmarked;

    const method = wasBookmarked ? 'delete' : 'post';
    const routeName = wasBookmarked ? route('posts.unbookmark', props.postId) : route('posts.bookmark', props.postId);

    router[method](routeName, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Reload only userBookmarks data to sync with server
            router.reload({
                only: ['userBookmarks'],
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    // Sync local state with server state after reload
                    // The prop will be updated from server
                },
            });
        },
        onError: (errors) => {
            // Revert optimistic update on error
            localIsBookmarked.value = wasBookmarked;
            
            // Error 429 will be handled by the exception handler
            if (errors && typeof errors === 'object' && 'message' in errors) {
                console.error('Bookmark error:', errors.message);
            }
        },
    });
};
</script>

<template>
    <button
        @click="toggleBookmark"
        :disabled="!canBookmark"
        :class="[
            'flex items-center gap-1 px-3 py-1 rounded-md text-sm font-medium transition-all',
            localIsBookmarked
                ? 'bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 shadow-sm'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
            !canBookmark && 'opacity-50 cursor-not-allowed'
        ]"
        :title="localIsBookmarked ? 'Remove bookmark' : 'Bookmark this post'"
    >
        <svg
            v-if="localIsBookmarked"
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
        <span>{{ localIsBookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
    </button>
</template>

