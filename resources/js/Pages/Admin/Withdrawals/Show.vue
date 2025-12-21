<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

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

const complete = () => {
    if (confirm('Mark this withdrawal as completed? This will deduct the balance from user account.')) {
        router.post(route('admin.withdrawals.complete', props.withdrawal.id));
    }
};
</script>

<template>
    <Head title="Review Withdrawal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Review Withdrawal
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <Link
                    :href="route('admin.withdrawals.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to Withdrawals
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                    <!-- Withdrawal Details -->
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Withdrawal Details</h3>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold mb-2">User</h4>
                                <p>{{ withdrawal.user?.name }} ({{ withdrawal.user?.email }})</p>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-2">Amount</h4>
                                <p class="text-xl text-blue-600">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(withdrawal.amount) }}
                                </p>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-2">Method</h4>
                                <p>{{ withdrawal.method === 'bank_transfer' ? 'Bank Transfer' : 'E-Wallet' }}</p>
                                <p v-if="withdrawal.bank_name">{{ withdrawal.bank_name }}</p>
                                <p v-if="withdrawal.ewallet_type">{{ withdrawal.ewallet_type }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-2">Account Details</h4>
                                <p>Account Number: {{ withdrawal.account_number }}</p>
                                <p>Account Name: {{ withdrawal.account_name }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-2">Status</h4>
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 rounded-full text-sm',
                                        withdrawal.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                        withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    ]"
                                >
                                    {{ withdrawal.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="withdrawal.status === 'pending'" class="border-t pt-6 space-y-4">
                        <h4 class="font-semibold">Actions</h4>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2">Admin Notes (optional for approval)</label>
                            <textarea
                                v-model="approveForm.admin_notes"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-2"
                            ></textarea>
                            <button
                                @click="approve"
                                :disabled="approveForm.processing"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                            >
                                Approve
                            </button>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Rejection Reason *</label>
                            <textarea
                                v-model="rejectForm.admin_notes"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-2"
                            ></textarea>
                            <button
                                @click="reject"
                                :disabled="rejectForm.processing"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                            >
                                Reject
                            </button>
                        </div>
                    </div>

                    <div v-else-if="withdrawal.status === 'approved'" class="border-t pt-6">
                        <button
                            @click="complete"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Mark as Completed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

