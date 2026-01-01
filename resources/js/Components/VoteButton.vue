<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
    upvotes: {
        type: Number,
        default: 0,
    },
    downvotes: {
        type: Number,
        default: 0,
    },
    userVote: {
        type: String,
        default: null,
    },
    canVote: {
        type: Boolean,
        default: true,
    },
});

const vote = (voteType) => {
    if (!props.canVote) return;

    router.post(route('votes.post', props.postId), {
        vote_type: voteType,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            // Error 429 will be handled by the exception handler
            // This is just a fallback
            if (errors && typeof errors === 'object' && 'message' in errors) {
                console.error('Vote error:', errors.message);
            }
        },
    });
};

const hasUpvoted = computed(() => props.userVote === 'upvote');
const hasDownvoted = computed(() => props.userVote === 'downvote');
</script>

<template>
    <div class="flex items-center gap-2">
        <button
            @click="vote('upvote')"
            :disabled="!canVote"
            :class="[
                'flex items-center gap-1 px-3 py-1 rounded-md text-sm transition',
                hasUpvoted
                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                !canVote && 'opacity-50 cursor-not-allowed'
            ]"
        >
            👍 {{ upvotes }}
        </button>
        <button
            @click="vote('downvote')"
            :disabled="!canVote"
            :class="[
                'flex items-center gap-1 px-3 py-1 rounded-md text-sm transition',
                hasDownvoted
                    ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                !canVote && 'opacity-50 cursor-not-allowed'
            ]"
        >
            👎 {{ downvotes }}
        </button>
    </div>
</template>

