<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
    repostsCount: {
        type: Number,
        default: 0,
    },
    isReposted: {
        type: Boolean,
        default: false,
    },
    canRepost: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();

// Local state for optimistic update
const localIsReposted = ref(props.isReposted);
const localRepostsCount = ref(props.repostsCount);

// Watch prop changes
watch(() => props.isReposted, (newVal) => {
    localIsReposted.value = newVal;
});

watch(() => props.repostsCount, (newVal) => {
    localRepostsCount.value = newVal;
});

const toggleRepost = () => {
    if (!props.canRepost) return;

    // Store the current valid URL before making the request
    // This prevents navigation to repost route if URL changes after error
    const validUrl = page.url && !page.url.includes('/repost') 
        ? page.url 
        : (window.location.pathname.includes('/repost') ? route('home') : window.location.pathname);

    // Optimistic update - immediately change UI
    const wasReposted = localIsReposted.value;
    localIsReposted.value = !wasReposted;
    
    if (wasReposted) {
        localRepostsCount.value = Math.max(0, localRepostsCount.value - 1);
    } else {
        localRepostsCount.value = localRepostsCount.value + 1;
    }

    const method = wasReposted ? 'delete' : 'post';
    const routeName = wasReposted 
        ? route('posts.unrepost', props.postId) 
        : route('posts.repost', props.postId);

    router[method](routeName, {}, {
        preserveScroll: true,
        preserveState: true,
        replace: false, // Don't replace current page
        only: [], // Don't reload any props automatically
        onBefore: () => {
            // Prevent any navigation before the request
            return true;
        },
        onSuccess: () => {
            // Reload only userReposts and posts data to sync with server
            // Use the stored valid URL to prevent GET requests to repost route
            const currentUrl = window.location.pathname;
            if (!currentUrl.includes('/repost') && validUrl && !validUrl.includes('/repost')) {
                // Use visit with the valid URL instead of reload to ensure we're on the right page
                router.visit(validUrl, {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['userReposts', 'posts'],
                });
            } else {
                // Fallback to home if we detect we're on a repost route
                router.visit(route('home'), {
                    preserveScroll: true,
                    preserveState: true,
                    only: ['userReposts', 'posts'],
                });
            }
        },
        onError: (errors) => {
            // Revert optimistic update on error
            localIsReposted.value = wasReposted;
            localRepostsCount.value = props.repostsCount;
            
            // Prevent any navigation on error - just show error message
            // Error 429 will be handled by the exception handler
            // Don't call any router methods here to prevent navigation
            if (errors && typeof errors === 'object' && 'message' in errors) {
                console.error('Repost error:', errors.message);
            }
            
            // If we're on a repost route after error, navigate away
            const currentUrl = window.location.pathname;
            if (currentUrl.includes('/repost')) {
                // Use setTimeout to avoid navigation during error handling
                setTimeout(() => {
                    router.visit(validUrl || route('home'), {
                        preserveScroll: true,
                        preserveState: true,
                    });
                }, 100);
            }
        },
    });
};
</script>

<template>
    <button
        @click="toggleRepost"
        :disabled="!canRepost"
        :class="[
            'flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition',
            localIsReposted && 'text-indigo-600 dark:text-indigo-400',
            !canRepost && 'opacity-50 cursor-not-allowed'
        ]"
        :title="localIsReposted ? 'Remove repost' : 'Repost this post'"
    >
        <svg
            class="w-5 h-5"
            :class="localIsReposted && 'fill-current'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
        </svg>
        <span v-if="localRepostsCount > 0">{{ localRepostsCount }}</span>
    </button>
</template>

