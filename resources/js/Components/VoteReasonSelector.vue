<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    voteType: {
        type: String,
        required: true,
        validator: (value) => ['upvote', 'downvote'].includes(value),
    },
    modelValue: {
        type: String,
        default: null,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'update:show', 'confirm', 'cancel']);

const selectedReason = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    selectedReason.value = newVal;
});

const upvoteReasons = {
    helpful: 'Helpful',
    accurate: 'Accurate',
    well_written: 'Well Written',
    informative: 'Informative',
};

const downvoteReasons = {
    misleading: 'Misleading',
    inaccurate: 'Inaccurate',
    spam: 'Spam',
    off_topic: 'Off Topic',
};

const reasons = computed(() => {
    return props.voteType === 'upvote' ? upvoteReasons : downvoteReasons;
});

const selectReason = (reason) => {
    selectedReason.value = reason;
    emit('update:modelValue', reason);
};

const confirm = () => {
    emit('confirm', selectedReason.value);
    emit('update:show', false);
};

const cancel = () => {
    selectedReason.value = null;
    emit('update:modelValue', null);
    emit('cancel');
    emit('update:show', false);
};

const skip = () => {
    selectedReason.value = null;
    emit('update:modelValue', null);
    emit('confirm', null);
    emit('update:show', false);
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="cancel"
            >
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-sm w-full mx-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ voteType === 'upvote' ? 'Why are you upvoting?' : 'Why are you downvoting?' }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Select a reason (optional)
                        </p>
                    </div>

                    <div class="p-4 space-y-2">
                        <button
                            v-for="(label, key) in reasons"
                            :key="key"
                            @click="selectReason(key)"
                            :class="[
                                'w-full px-4 py-3 text-left rounded-lg transition-colors',
                                selectedReason === key
                                    ? voteType === 'upvote'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-2 border-green-500'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-2 border-red-500'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-2 border-transparent'
                            ]"
                        >
                            {{ label }}
                        </button>
                    </div>

                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                        <button
                            @click="skip"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            Skip
                        </button>
                        <button
                            @click="confirm"
                            :disabled="!selectedReason"
                            :class="[
                                'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                selectedReason
                                    ? voteType === 'upvote'
                                        ? 'bg-green-600 text-white hover:bg-green-700'
                                        : 'bg-red-600 text-white hover:bg-red-700'
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed dark:bg-gray-600 dark:text-gray-400'
                            ]"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

