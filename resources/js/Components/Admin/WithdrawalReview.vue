<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    withdrawal: Object,
});

const approveForm = useForm({
    admin_notes: '',
});

const rejectForm = useForm({
    admin_notes: '',
});

const approve = () => {
    approveForm.post(route('admin.withdrawals.approve', props.withdrawal.id));
};

const reject = () => {
    if (!rejectForm.admin_notes) {
        alert('Admin notes are required for rejection');
        return;
    }
    rejectForm.post(route('admin.withdrawals.reject', props.withdrawal.id));
};
</script>

<template>
    <div class="space-y-4">
        <div v-if="withdrawal.status === 'pending'">
            <h3 class="font-semibold mb-4">Review Actions</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Admin Notes (optional)</label>
                    <textarea
                        v-model="approveForm.admin_notes"
                        class="w-full px-4 py-2 border rounded-lg"
                        rows="3"
                    ></textarea>
                    <button
                        @click="approve"
                        :disabled="approveForm.processing"
                        class="mt-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                    >
                        Approve
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Rejection Reason *</label>
                    <textarea
                        v-model="rejectForm.admin_notes"
                        required
                        class="w-full px-4 py-2 border rounded-lg"
                        rows="3"
                    ></textarea>
                    <button
                        @click="reject"
                        :disabled="rejectForm.processing"
                        class="mt-2 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

