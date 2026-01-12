<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    verification: Object,
});

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    reason: '',
});

const revokeForm = useForm({
    reason: '',
});

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showRevokeModal = ref(false);

const approve = () => {
    approveForm.post(route('admin.seller-verifications.approve', props.verification.id), {
        preserveScroll: true,
        onSuccess: () => {
            showApproveModal.value = false;
            approveForm.reset();
        },
    });
};

const reject = () => {
    rejectForm.post(route('admin.seller-verifications.reject', props.verification.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const revoke = () => {
    revokeForm.post(route('admin.sellers.verification.revoke', props.verification.seller_id), {
        preserveScroll: true,
        onSuccess: () => {
            showRevokeModal.value = false;
            revokeForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Verification Application Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Verification Application Details
                </h2>
                <Link
                    :href="route('admin.seller-verifications.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Application Status -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                Application Status
                            </h3>
                            <span
                                :class="[
                                    'px-3 py-1 text-sm font-semibold rounded-full',
                                    verification?.status === 'pending'
                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        : verification?.status === 'approved'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                ]"
                            >
                                {{ verification?.status }}
                            </span>
                        </div>
                        <div v-if="verification?.status === 'pending'" class="flex gap-3">
                            <button
                                @click="showApproveModal = true"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                            >
                                Approve
                            </button>
                            <button
                                @click="showRejectModal = true"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                Reject
                            </button>
                        </div>
                        <div v-else-if="verification?.status === 'approved'" class="flex gap-3">
                            <button
                                @click="showRevokeModal = true"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                Revoke Verification
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Seller Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Seller Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.seller?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.seller?.email || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Member Since</p>
                            <p class="text-gray-900 dark:text-white">{{ new Date(verification?.seller?.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Application Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Name</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.application_data?.business_name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Type</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.application_data?.business_type || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Address</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.application_data?.business_address || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tax ID</p>
                            <p class="text-gray-900 dark:text-white">{{ verification?.application_data?.tax_id || 'N/A' }}</p>
                        </div>
                        <div v-if="verification?.application_data?.additional_info">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Additional Information</p>
                            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ verification.application_data.additional_info }}</p>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div v-if="verification?.application_data?.documents" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Supporting Documents
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="(doc, index) in verification.application_data.documents"
                            :key="index"
                            class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                        >
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Document {{ index + 1 }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ doc.name || 'Document' }}
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="doc.url || doc.path"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm"
                            >
                                View Document
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Approval/Rejection Modals -->
                <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Approve Verification
                        </h3>
                        <form @submit.prevent="approve" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Notes (optional)
                                </label>
                                <textarea
                                    v-model="approveForm.notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div class="flex gap-3 justify-end">
                                <button
                                    type="button"
                                    @click="showApproveModal = false"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="approveForm.processing"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                                >
                                    Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Reject Verification
                        </h3>
                        <form @submit.prevent="reject" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Rejection Reason *
                                </label>
                                <textarea
                                    v-model="rejectForm.reason"
                                    required
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div class="flex gap-3 justify-end">
                                <button
                                    type="button"
                                    @click="showRejectModal = false"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="rejectForm.processing"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                                >
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-if="showRevokeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Revoke Verification
                        </h3>
                        <form @submit.prevent="revoke" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Reason *
                                </label>
                                <textarea
                                    v-model="revokeForm.reason"
                                    required
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div class="flex gap-3 justify-end">
                                <button
                                    type="button"
                                    @click="showRevokeModal = false"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="revokeForm.processing"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                                >
                                    Revoke
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

