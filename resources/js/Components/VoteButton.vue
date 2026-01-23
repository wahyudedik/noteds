<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import VoteReasonSelector from '@/Components/VoteReasonSelector.vue';

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
    weightedUpvotes: {
        type: [Number, String],
        default: null,
    },
    weightedDownvotes: {
        type: [Number, String],
        default: null,
    },
    userVote: {
        type: String,
        default: null,
    },
    canVote: {
        type: Boolean,
        default: true,
    },
    useWeighted: {
        type: Boolean,
        default: false,
    },
    isAuthor: {
        type: Boolean,
        default: false,
    },
    disabledReason: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['viewAnalytics']);

const showReasonSelector = ref(false);
const pendingVoteType = ref(null);
const selectedReason = ref(null);

const vote = (voteType) => {
    if (!props.canVote) return;

    // Show reason selector
    pendingVoteType.value = voteType;
    showReasonSelector.value = true;
};

const confirmVote = (reason) => {
    router.post(route('votes.post', props.postId), {
        vote_type: pendingVoteType.value,
        reason: reason,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: (errors) => {
            if (errors && typeof errors === 'object' && 'message' in errors) {
                console.error('Vote error:', errors.message);
            }
        },
    });

    showReasonSelector.value = false;
    pendingVoteType.value = null;
    selectedReason.value = null;
};

const cancelVote = () => {
    showReasonSelector.value = false;
    pendingVoteType.value = null;
    selectedReason.value = null;
};

const hasUpvoted = computed(() => props.userVote === 'upvote');
const hasDownvoted = computed(() => props.userVote === 'downvote');

const displayUpvotes = computed(() => {
    if (props.useWeighted && props.weightedUpvotes !== null && props.weightedUpvotes !== undefined) {
        // Convert string to number if needed
        const value = typeof props.weightedUpvotes === 'string' 
            ? parseFloat(props.weightedUpvotes) 
            : props.weightedUpvotes;
        return isNaN(value) ? 0 : value;
    }
    return props.upvotes;
});

const displayDownvotes = computed(() => {
    if (props.useWeighted && props.weightedDownvotes !== null && props.weightedDownvotes !== undefined) {
        // Convert string to number if needed
        const value = typeof props.weightedDownvotes === 'string' 
            ? parseFloat(props.weightedDownvotes) 
            : props.weightedDownvotes;
        return isNaN(value) ? 0 : value;
    }
    return props.downvotes;
});

const formatNumber = (num) => {
    // Handle null, undefined, or invalid values
    if (num === null || num === undefined || num === '') {
        return '0';
    }
    
    // Convert string to number if needed
    const number = typeof num === 'string' ? parseFloat(num) : num;
    
    // Check if conversion was successful
    if (isNaN(number)) {
        return '0';
    }
    
    if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'k';
    }
    
    // Check if it's an integer or decimal
    return Number.isInteger(number) ? number.toString() : number.toFixed(1);
};

const viewAnalytics = () => {
    emit('viewAnalytics');
};
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
            :title="!canVote ? (disabledReason || 'Vote tidak diizinkan') : 'Upvote'"
        >
            👍 {{ formatNumber(displayUpvotes) }}
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
            :title="!canVote ? (disabledReason || 'Vote tidak diizinkan') : 'Downvote'"
        >
            👎 {{ formatNumber(displayDownvotes) }}
        </button>

        <!-- View Analytics button (author only) -->
        <button
            v-if="isAuthor"
            @click="viewAnalytics"
            class="flex items-center gap-1 px-2 py-1 rounded-md text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            title="View vote analytics"
        >
            📊
        </button>
    </div>

    <!-- Vote Reason Selector Modal -->
    <VoteReasonSelector
        v-if="pendingVoteType"
        v-model="selectedReason"
        v-model:show="showReasonSelector"
        :vote-type="pendingVoteType"
        @confirm="confirmVote"
        @cancel="cancelVote"
    />
</template>
