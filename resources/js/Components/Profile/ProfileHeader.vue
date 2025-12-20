<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Cover Image Placeholder -->
        <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>

        <!-- Profile Info -->
        <div class="px-6 pb-6">
            <div class="flex items-end -mt-16 mb-4">
                <div class="h-24 w-24 rounded-full bg-indigo-500 border-4 border-white dark:border-gray-800 flex items-center justify-center text-white text-3xl font-bold">
                    {{ (profileUser.business_name || profileUser.name).charAt(0).toUpperCase() }}
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ profileUser.business_name || profileUser.name }}
                            </h1>
                            <p v-if="profileUser.business_name" class="text-gray-600 dark:text-gray-400">
                                {{ profileUser.name }}
                            </p>
                        </div>
                        <Link
                            v-if="isOwnProfile"
                            :href="route('profile.edit')"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition"
                        >
                            Edit Profile
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="profileUser.business_field" class="text-gray-600 dark:text-gray-400 mb-4">
                {{ profileUser.business_field }}
            </div>

            <div v-if="profileUser.is_verified_mentor" class="mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    ✓ Verified Mentor
                </span>
            </div>

            <!-- Links -->
            <div v-if="profileUser.portfolio_url || profileUser.website_url" class="flex gap-3">
                <a
                    v-if="profileUser.portfolio_url"
                    :href="profileUser.portfolio_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition"
                >
                    Portfolio
                </a>
                <a
                    v-if="profileUser.website_url"
                    :href="profileUser.website_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition"
                >
                    Website
                </a>
            </div>
        </div>
    </div>
</template>

