<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    poll: {
        type: Object,
        required: true,
    },
    userVote: {
        type: Object,
        default: null,
    },
    postId: {
        type: String,
        required: true,
    },
});

const selectedOption = ref(props.userVote?.poll_option_id || null);
const hasVoted = computed(() => !!props.userVote);
const isExpired = computed(() => {
    if (!props.poll.ends_at) return false;
    return new Date(props.poll.ends_at) < new Date();
});

const vote = () => {
    if (!selectedOption.value || hasVoted.value || isExpired.value) return;

    router.post(route('polls.vote', [props.postId, props.poll.id]), {
        poll_option_id: selectedOption.value,
    }, {
        preserveScroll: true,
    });
};

const getPercentage = (option) => {
    if (!props.poll.votes_count || props.poll.votes_count === 0) return 0;
    return Math.round((option.votes_count / props.poll.votes_count) * 100);
};
</script>

<template>
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">
            {{ poll.question }}
        </h4>

        <div v-if="isExpired" class="text-sm text-gray-500 dark:text-gray-400 mb-2">
            Poll has ended
        </div>

        <div class="space-y-2">
            <label
                v-for="option in poll.options"
                :key="option.id"
                :class="[
                    'block p-3 rounded-lg border-2 cursor-pointer transition',
                    selectedOption === option.id
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900'
                        : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-700',
                    (hasVoted || isExpired) && 'cursor-default'
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-1">
                        <input
                            v-if="!hasVoted && !isExpired"
                            type="radio"
                            :value="option.id"
                            v-model="selectedOption"
                            class="text-indigo-600 focus:ring-indigo-500"
                        />
                        <span class="text-gray-900 dark:text-gray-100">{{ option.option_text }}</span>
                    </div>
                    <div v-if="hasVoted || isExpired" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ option.votes_count }} votes ({{ getPercentage(option) }}%)
                    </div>
                </div>
                <div
                    v-if="hasVoted || isExpired"
                    class="mt-2 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden"
                >
                    <div
                        class="h-full bg-indigo-500 transition-all"
                        :style="{ width: `${getPercentage(option)}%` }"
                    ></div>
                </div>
            </label>
        </div>

        <div v-if="!hasVoted && !isExpired" class="mt-4">
            <PrimaryButton @click="vote" :disabled="!selectedOption">
                Vote
            </PrimaryButton>
        </div>

        <div v-if="hasVoted || isExpired" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Total votes: {{ poll.votes_count }}
        </div>
    </div>
</template>

