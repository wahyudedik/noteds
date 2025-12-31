<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    clip: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['approved', 'rejected']);

const approveForm = useForm({});
const rejectForm = useForm({
    rejection_reason: '',
});

const showRejectModal = ref(false);

const approve = () => {
    if (confirm('Are you sure you want to approve this clip?')) {
        approveForm.post(route('admin.clips.approve', props.clip.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('approved');
            },
        });
    }
};

const reject = () => {
    rejectForm.post(route('admin.clips.reject', props.clip.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
            emit('rejected');
        },
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Review Actions
        </h3>
        
        <div v-if="clip.status === 'pending'" class="flex flex-wrap gap-3">
            <button
                @click="approve"
                :disabled="approveForm.processing"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Approve Clip
            </button>
            <button
                @click="showRejectModal = true"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject Clip
            </button>
        </div>

        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            <span class="capitalize">{{ clip.status }}</span> clip - No actions available
        </div>
    </div>

    <!-- Reject Modal -->
    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showRejectModal = false"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Reject Clip
            </h3>
            <form @submit.prevent="reject">
                <div class="mb-4">
                    <InputLabel for="rejection_reason" value="Rejection Reason *" />
                    <Textarea
                        id="rejection_reason"
                        class="mt-1 block w-full"
                        v-model="rejectForm.rejection_reason"
                        required
                        rows="4"
                        placeholder="Enter reason for rejection..."
                    />
                    <InputError class="mt-2" :message="rejectForm.errors.rejection_reason" />
                </div>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        @click="showRejectModal = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <PrimaryButton :disabled="rejectForm.processing">
                        Reject Clip
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

