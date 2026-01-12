<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VerifiedBadge from '@/Components/Marketplace/Seller/VerifiedBadge.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    verification: Object,
    canApply: Boolean,
    eligibilityErrors: Array,
});

const form = useForm({
    business_name: '',
    business_type: '',
    business_address: '',
    tax_id: '',
    documents: [],
    additional_info: '',
});

const documentPreview = ref([]);

const handleDocumentsChange = (event) => {
    const files = Array.from(event.target.files);
    form.documents = files;
    documentPreview.value = files.map(file => ({
        name: file.name,
        size: file.size,
        url: URL.createObjectURL(file),
    }));
};

const submit = () => {
    form.post(route('marketplace.seller.verification.apply'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Seller Verification" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Seller Verification
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Status Display -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                Verification Status
                            </h3>
                            <div class="flex items-center gap-4">
                                <VerifiedBadge :seller="$page.props.auth.user" size="lg" />
                                <span
                                    v-if="verification?.status === 'pending'"
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                >
                                    Pending Review
                                </span>
                                <span
                                    v-else-if="verification?.status === 'approved'"
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                >
                                    Verified
                                </span>
                                <span
                                    v-else-if="verification?.status === 'rejected'"
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                >
                                    Rejected
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Reason -->
                    <div v-if="verification?.status === 'rejected' && verification?.rejection_reason" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                            Rejection Reason:
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            {{ verification.rejection_reason }}
                        </p>
                    </div>

                    <!-- Eligibility Errors -->
                    <div v-if="eligibilityErrors && eligibilityErrors.length > 0" class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                            Eligibility Requirements:
                        </p>
                        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                            <li v-for="error in eligibilityErrors" :key="error">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Application Form -->
                <div v-if="canApply" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Submit Verification Application
                    </h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Business Name *
                            </label>
                            <input
                                v-model="form.business_name"
                                type="text"
                                required
                                maxlength="255"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Business Type *
                            </label>
                            <input
                                v-model="form.business_type"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Business Address *
                            </label>
                            <textarea
                                v-model="form.business_address"
                                required
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tax ID
                            </label>
                            <input
                                v-model="form.tax_id"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Supporting Documents * (PDF/Images, max 5MB each)
                            </label>
                            <input
                                @change="handleDocumentsChange"
                                type="file"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Upload at least 1 document (PDF, JPG, JPEG, PNG)
                            </p>
                            <div v-if="documentPreview.length > 0" class="mt-2 space-y-2">
                                <div
                                    v-for="(doc, index) in documentPreview"
                                    :key="index"
                                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ doc.name }} ({{ (doc.size / 1024 / 1024).toFixed(2) }} MB)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Additional Information
                            </label>
                            <textarea
                                v-model="form.additional_info"
                                rows="4"
                                maxlength="1000"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ form.additional_info?.length || 0 }}/1000 characters
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Submit Application
                        </button>
                    </form>
                </div>

                <!-- Application History -->
                <div v-if="verification && verification.status !== 'approved'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Application Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Name</p>
                            <p class="text-gray-900 dark:text-white">{{ verification.application_data?.business_name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Type</p>
                            <p class="text-gray-900 dark:text-white">{{ verification.application_data?.business_type || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Business Address</p>
                            <p class="text-gray-900 dark:text-white">{{ verification.application_data?.business_address || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Submitted At</p>
                            <p class="text-gray-900 dark:text-white">{{ new Date(verification.created_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

