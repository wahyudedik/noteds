<script setup>
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Textarea from '@/Components/Textarea.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    reportableType: {
        type: String,
        required: true,
        validator: (value) => ['post', 'comment', 'user'].includes(value),
    },
    reportableId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close', 'reported']);

const reportReasons = [
    { value: 'spam', label: 'Spam', description: 'Repetitive, unwanted, or unsolicited content' },
    { value: 'harassment', label: 'Harassment', description: 'Bullying, threats, or abusive behavior' },
    { value: 'inappropriate', label: 'Inappropriate Content', description: 'Content that violates community guidelines' },
    { value: 'copyright', label: 'Copyright Violation', description: 'Unauthorized use of copyrighted material' },
    { value: 'fake', label: 'Fake or Misleading', description: 'False information or impersonation' },
    { value: 'other', label: 'Other', description: 'Other reason not listed above' },
];

const form = useForm({
    reason: '',
    notes: '',
});

const selectedReason = computed(() => {
    return reportReasons.find(r => r.value === form.reason);
});

const submit = () => {
    if (!form.reason) {
        form.setError('reason', 'Please select a reason for reporting.');
        return;
    }

    let routeName = '';
    if (props.reportableType === 'post') {
        routeName = 'posts.report';
    } else if (props.reportableType === 'comment') {
        routeName = 'comments.report';
    } else if (props.reportableType === 'user') {
        routeName = 'users.report';
    }

    form.post(route(routeName, props.reportableId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('reported');
        },
    });
};

const close = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Report {{ reportableType.charAt(0).toUpperCase() + reportableType.slice(1) }}
                </h2>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Help us understand what's wrong. Your report will be reviewed by our moderation team.
            </p>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Reason Selection -->
                <div>
                    <InputLabel value="Reason for reporting" />
                    <div class="mt-2 space-y-2">
                        <label
                            v-for="reason in reportReasons"
                            :key="reason.value"
                            :class="[
                                'flex items-start p-3 rounded-lg border-2 cursor-pointer transition-colors',
                                form.reason === reason.value
                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <input
                                type="radio"
                                :value="reason.value"
                                v-model="form.reason"
                                class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                            />
                            <div class="ml-3 flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ reason.label }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ reason.description }}
                                </div>
                            </div>
                        </label>
                    </div>
                    <InputError class="mt-2" :message="form.errors.reason" />
                </div>

                <!-- Additional Notes -->
                <div>
                    <InputLabel for="notes" value="Additional Details (Optional)" />
                    <Textarea
                        id="notes"
                        class="mt-1 block w-full"
                        v-model="form.notes"
                        rows="4"
                        placeholder="Provide any additional context that might help us understand the issue..."
                    />
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <PrimaryButton :disabled="form.processing">
                        Submit Report
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

