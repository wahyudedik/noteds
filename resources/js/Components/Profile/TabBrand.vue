<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    brandRegistration: {
        type: Object,
        default: null,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
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
    <div class="space-y-6">
        <div v-if="brandRegistration" class="space-y-6">
            <!-- Company Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Company Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Company/Brand Name</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ brandRegistration.company_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Business Type</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white capitalize">
                            {{ brandRegistration.business_type }}
                        </div>
                    </div>

                    <div v-if="brandRegistration.website_url">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Website</div>
                        <a
                            :href="brandRegistration.website_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            {{ brandRegistration.website_url }}
                        </a>
                    </div>

                    <div v-if="brandRegistration.description">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Description</div>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ brandRegistration.description }}
                        </p>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                        <span
                            :class="['inline-flex px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(brandRegistration.status)]"
                        >
                            {{ brandRegistration.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Contact Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Contact Name</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">
                            {{ brandRegistration.contact_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Contact Email</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">
                            {{ brandRegistration.contact_email }}
                        </div>
                    </div>

                    <div v-if="brandRegistration.contact_phone">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Contact Phone</div>
                        <div class="text-base font-medium text-gray-900 dark:text-white">
                            {{ brandRegistration.contact_phone }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions for own profile -->
            <div v-if="isOwnProfile" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <Link
                    :href="route('clipper.campaigns.index')"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    View My Campaigns
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ isOwnProfile ? 'You haven\'t registered as a Brand yet.' : 'This user hasn\'t registered as a Brand.' }}
            </p>
            <Link
                v-if="isOwnProfile"
                :href="route('clipper.brand-registration.create')"
                class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Register as Brand
            </Link>
        </div>
    </div>
</template>

