<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    registration: {
        type: Object,
        required: true,
    },
});

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    rejection_reason: '',
});

const approve = () => {
    approveForm.post(route('admin.brand-approvals.approve', props.registration.id), {
        preserveScroll: true,
    });
};

const reject = () => {
    if (!rejectForm.rejection_reason.trim()) {
        rejectForm.setError('rejection_reason', 'Rejection reason is required.');
        return;
    }
    
    rejectForm.post(route('admin.brand-approvals.reject', props.registration.id), {
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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
    <Head :title="registration.company_name + ' - Brand Approval'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Brand Approval Review
                </h2>
                <Link
                    :href="route('admin.brand-approvals.index')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- Status Badge -->
                <div class="mb-6">
                    <span
                        :class="['inline-flex px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(registration.status)]"
                    >
                        {{ registration.status }}
                    </span>
                </div>

                <!-- Company Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        Company Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Company/Brand Name</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ registration.company_name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Business Type</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white capitalize">
                                {{ registration.business_type }}
                            </div>
                        </div>

                        <div v-if="registration.website_url">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Website</div>
                            <a
                                :href="registration.website_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-600 dark:text-blue-400 hover:underline"
                            >
                                {{ registration.website_url }}
                            </a>
                        </div>

                        <div v-if="registration.description">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Description</div>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ registration.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        Contact Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Contact Name</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ registration.contact_name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Contact Email</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ registration.contact_email }}
                            </div>
                        </div>

                        <div v-if="registration.contact_phone">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Contact Phone</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ registration.contact_phone }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                        User Information
                    </h3>
                    
                    <div class="space-y-2">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">User</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ registration.user?.name || registration.user?.email }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ registration.user?.email }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Submitted</div>
                            <div class="text-base font-medium text-gray-900 dark:text-white">
                                {{ formatDate(registration.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason (if rejected) -->
                <div v-if="registration.status === 'rejected' && registration.rejection_reason" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-200 mb-2">
                        Rejection Reason
                    </h3>
                    <p class="text-red-800 dark:text-red-200 whitespace-pre-line">
                        {{ registration.rejection_reason }}
                    </p>
                </div>

                <!-- Approval Notes (if approved) -->
                <div v-if="registration.status === 'approved' && registration.approval_notes" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-200 mb-2">
                        Approval Notes
                    </h3>
                    <p class="text-green-800 dark:text-green-200 whitespace-pre-line">
                        {{ registration.approval_notes }}
                    </p>
                </div>

                <!-- Actions (if pending) -->
                <div v-if="registration.status === 'pending'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Actions
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Approve Form -->
                        <form @submit.prevent="approve" class="space-y-3">
                            <div>
                                <label for="approval_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Approval Notes (Optional)
                                </label>
                                <textarea
                                    id="approval_notes"
                                    v-model="approveForm.notes"
                                    rows="3"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
                                    placeholder="Add any notes about this approval..."
                                ></textarea>
                            </div>
                            <PrimaryButton
                                type="submit"
                                :disabled="approveForm.processing"
                                class="bg-green-600 hover:bg-green-700 focus:ring-green-500"
                            >
                                {{ approveForm.processing ? 'Approving...' : 'Approve Registration' }}
                            </PrimaryButton>
                        </form>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <!-- Reject Form -->
                            <form @submit.prevent="reject" class="space-y-3">
                                <div>
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rejection Reason *
                                    </label>
                                    <textarea
                                        id="rejection_reason"
                                        v-model="rejectForm.rejection_reason"
                                        rows="3"
                                        required
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500"
                                        placeholder="Please provide a reason for rejection..."
                                    ></textarea>
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ rejectForm.errors.rejection_reason }}
                                    </p>
                                </div>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="rejectForm.processing"
                                    class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
                                >
                                    {{ rejectForm.processing ? 'Rejecting...' : 'Reject Registration' }}
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

