<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    registration: Object,
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    rejection_reason: '',
});

const approve = () => {
    approveForm.post(route('admin.clipper-approvals.approve', props.registration.id), {
        preserveScroll: true,
        onSuccess: () => {
            showApproveModal.value = false;
            approveForm.reset();
        },
    });
};

const reject = () => {
    rejectForm.post(route('admin.clipper-approvals.reject', props.registration.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.pending;
};
</script>

<template>
    <Head :title="`Clipper Approval - ${registration.user?.name || 'N/A'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clipper Approval Details
                </h2>
                <Link
                    :href="route('admin.clipper-approvals.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                >
                    ← Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Status</h3>
                            <span
                                :class="[
                                    'mt-2 inline-block px-3 py-1 text-sm font-medium rounded-full',
                                    getStatusBadgeClass(registration.status)
                                ]"
                            >
                                {{ registration.status }}
                            </span>
                        </div>
                        <div v-if="registration.status === 'pending'" class="flex gap-3">
                            <button
                                @click="showApproveModal = true"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                            >
                                Approve
                            </button>
                            <button
                                @click="showRejectModal = true"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">User Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ registration.user?.name || 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ registration.user?.email || 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ new Date(registration.created_at).toLocaleString() }}
                            </dd>
                        </div>
                        <div v-if="registration.approved_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ new Date(registration.approved_at).toLocaleString() }}
                            </dd>
                        </div>
                        <div v-if="registration.rejected_at">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ new Date(registration.rejected_at).toLocaleString() }}
                            </dd>
                        </div>
                        <div v-if="registration.admin">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ registration.status === 'approved' ? 'Approved By' : 'Rejected By' }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ registration.admin?.name || 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Registration Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Registration Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Portfolio URL</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a
                                    v-if="registration.portfolio_url"
                                    :href="registration.portfolio_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 break-all"
                                >
                                    {{ registration.portfolio_url }}
                                </a>
                                <span v-else class="text-gray-400">Not provided</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Skills</dt>
                            <dd class="mt-1">
                                <div v-if="registration.skills && Array.isArray(registration.skills) && registration.skills.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="skill in registration.skills"
                                        :key="skill"
                                        class="inline-block px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-full"
                                    >
                                        {{ skill }}
                                    </span>
                                </div>
                                <span v-else class="text-sm text-gray-400">Not provided</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Goals</dt>
                            <dd class="mt-1">
                                <div v-if="registration.goals && Array.isArray(registration.goals) && registration.goals.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="goal in registration.goals"
                                        :key="goal"
                                        class="inline-block px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-full"
                                    >
                                        {{ goal }}
                                    </span>
                                </div>
                                <span v-else class="text-sm text-gray-400">Not provided</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Admin Notes -->
                <div v-if="registration.admin_notes" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Admin Notes</h3>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ registration.admin_notes }}</p>
                </div>

                <!-- Approve Modal -->
                <div
                    v-if="showApproveModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                    @click.self="showApproveModal = false"
                >
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Approve Clipper Registration</h3>
                            <form @submit.prevent="approve">
                                <div class="mb-4">
                                    <label for="approve-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notes (Optional)
                                    </label>
                                    <textarea
                                        id="approve-notes"
                                        v-model="approveForm.notes"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Add any notes about this approval..."
                                    ></textarea>
                                    <div v-if="approveForm.errors.notes" class="mt-1 text-sm text-red-600">
                                        {{ approveForm.errors.notes }}
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button
                                        type="button"
                                        @click="showApproveModal = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 dark:bg-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="approveForm.processing"
                                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50"
                                    >
                                        {{ approveForm.processing ? 'Approving...' : 'Approve' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div
                    v-if="showRejectModal"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
                    @click.self="showRejectModal = false"
                >
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Reject Clipper Registration</h3>
                            <form @submit.prevent="reject">
                                <div class="mb-4">
                                    <label for="reject-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rejection Reason <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        id="reject-reason"
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Please provide a reason for rejection..."
                                    ></textarea>
                                    <div v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">
                                        {{ rejectForm.errors.rejection_reason }}
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button
                                        type="button"
                                        @click="showRejectModal = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 dark:bg-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="rejectForm.processing"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                                    >
                                        {{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
