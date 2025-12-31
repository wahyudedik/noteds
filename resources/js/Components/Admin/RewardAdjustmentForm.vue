<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    clip: {
        type: Object,
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'adjusted']);

const adjustRewardForm = useForm({
    reward_amount: props.clip?.approved_reward || props.clip?.pending_reward || 0,
    reason: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const currentReward = computed(() => {
    return props.clip?.approved_reward || props.clip?.pending_reward || 0;
});

const adjustReward = () => {
    adjustRewardForm.post(route('admin.clips.adjust-reward', props.clip.id), {
        preserveScroll: true,
        onSuccess: () => {
            adjustRewardForm.reset();
            emit('adjusted');
            emit('close');
        },
    });
};

const close = () => {
    adjustRewardForm.reset();
    adjustRewardForm.reward_amount = currentReward.value;
    emit('close');
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="close"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Adjust Reward
                </h3>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="adjustReward">
                <!-- Current Reward Info -->
                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Current Reward</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ formatCurrency(currentReward) }}
                    </div>
                </div>

                <!-- Reward Amount -->
                <div class="mb-4">
                    <InputLabel for="reward_amount" value="New Reward Amount *" />
                    <div class="mt-1 relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                        <TextInput
                            id="reward_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full pl-10"
                            v-model="adjustRewardForm.reward_amount"
                            required
                        />
                    </div>
                    <InputError class="mt-2" :message="adjustRewardForm.errors.reward_amount" />
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <InputLabel for="reason" value="Reason for Adjustment *" />
                    <Textarea
                        id="reason"
                        class="mt-1 block w-full"
                        v-model="adjustRewardForm.reason"
                        required
                        rows="3"
                        placeholder="Enter reason for adjustment (e.g., view validation adjustment, manual review, etc.)..."
                    />
                    <InputError class="mt-2" :message="adjustRewardForm.errors.reason" />
                </div>

                <!-- Difference Display -->
                <div v-if="adjustRewardForm.reward_amount && adjustRewardForm.reward_amount !== currentReward" class="mb-4 p-3 rounded-lg" :class="adjustRewardForm.reward_amount > currentReward ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Difference</div>
                    <div class="text-lg font-semibold" :class="adjustRewardForm.reward_amount > currentReward ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                        {{ adjustRewardForm.reward_amount > currentReward ? '+' : '' }}Rp {{ formatCurrency(Math.abs(adjustRewardForm.reward_amount - currentReward)) }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <PrimaryButton :disabled="adjustRewardForm.processing">
                        {{ adjustRewardForm.processing ? 'Adjusting...' : 'Adjust Reward' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

