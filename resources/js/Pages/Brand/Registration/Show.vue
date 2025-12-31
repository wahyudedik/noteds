<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    registration: {
        type: Object,
        required: true,
    },
});

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.pending;
};

const getStatusIcon = (status) => {
    const icons = {
        pending: '⏳',
        approved: '✅',
        rejected: '❌',
    };
    return icons[status] || icons.pending;
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
</script>

<template>
    <Head title="Brand Registration Status" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Brand Registration Status
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">{{ getStatusIcon(registration.status) }}</span>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    Registration Status
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Submitted on {{ formatDate(registration.created_at) }}
                                </p>
                            </div>
                        </div>
                        <span
                            :class="['px-4 py-2 text-sm font-medium rounded-full', getStatusBadgeClass(registration.status)]"
                        >
                            {{ registration.status }}
                        </span>
                    </div>

                    <div v-if="registration.status === 'pending'" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            Your registration is currently under review. Our admin team will review your application and notify you once a decision has been made.
                        </p>
                    </div>

                    <div v-if="registration.status === 'approved'" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <p class="text-sm text-green-800 dark:text-green-200 mb-2">
                            🎉 Congratulations! Your brand registration has been approved.
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            You can now create campaigns and start working with clippers. 
                            <Link :href="route('clipper.campaigns.create')" class="underline font-semibold">
                                Create your first campaign
                            </Link>
                        </p>
                    </div>

                    <div v-if="registration.status === 'rejected'" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <p class="text-sm text-red-800 dark:text-red-200 mb-2 font-semibold">
                            Your registration has been rejected.
                        </p>
                        <p v-if="registration.rejection_reason" class="text-sm text-red-700 dark:text-red-300 mt-2">
                            <strong>Reason:</strong> {{ registration.rejection_reason }}
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-2">
                            If you believe this is an error, please contact our support team or 
                            <Link :href="route('clipper.brand-registrations.create')" class="underline font-semibold">
                                submit a new registration
                            </Link>.
                        </p>
                    </div>
                </div>

                <!-- Registration Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Registration Details
                    </h3>

                    <div class="space-y-6">
                        <!-- Company Information -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Company Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Company Name</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.company_name }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Business Type</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.business_type }}
                                    </div>
                                </div>
                                <div v-if="registration.business_registration_number" class="md:col-span-2">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Business Registration Number</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.business_registration_number }}
                                    </div>
                                </div>
                                <div v-if="registration.description" class="md:col-span-2">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Description</div>
                                    <div class="text-base text-gray-900 dark:text-white mt-1">
                                        {{ registration.description }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Address
                            </h4>
                            <div class="text-base text-gray-900 dark:text-white">
                                {{ registration.address }}, {{ registration.city }}, {{ registration.province }} {{ registration.postal_code }}
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Contact Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Phone</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.phone }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.email }}
                                    </div>
                                </div>
                                <div v-if="registration.website" class="md:col-span-2">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Website</div>
                                    <a
                                        :href="registration.website"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-base font-medium text-blue-600 dark:text-blue-400 hover:underline mt-1"
                                    >
                                        {{ registration.website }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                                Contact Person
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Name</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.contact_person_name }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Position</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.contact_person_position }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.contact_person_email }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Phone</div>
                                    <div class="text-base font-medium text-gray-900 dark:text-white mt-1">
                                        {{ registration.contact_person_phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div v-if="registration.admin_notes" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                Admin Notes
                            </h4>
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ registration.admin_notes }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex gap-4">
                    <Link
                        :href="route('dashboard')"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Back to Dashboard
                    </Link>
                    <Link
                        v-if="registration.status === 'rejected'"
                        :href="route('clipper.brand-registrations.create')"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Submit New Registration
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

