<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
});

const displayName = computed(() => props.profileUser.business_name || props.profileUser.name);
const displaySubtitle = computed(() => {
    if (props.profileUser.business_name && props.profileUser.name) {
        return props.profileUser.name;
    }
    return props.profileUser.business_field || '';
});

const avatarInitial = computed(() => displayName.value.charAt(0).toUpperCase());
const avatarUrl = computed(() => props.profileUser.avatar_url || null);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Gradient Header -->
        <div class="h-32 bg-gradient-to-r from-purple-500 via-purple-600 to-purple-700"></div>

        <!-- Profile Info -->
        <div class="px-6 pb-6 relative">
            <!-- Avatar (overlapping header) -->
            <div class="flex items-end -mt-20 mb-4">
                <div class="relative">
                    <div 
                        class="h-32 w-32 rounded-full bg-indigo-500 border-4 border-white dark:border-gray-800 flex items-center justify-center text-white text-4xl font-bold overflow-hidden"
                    >
                        <img 
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            :alt="displayName"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>{{ avatarInitial }}</span>
                    </div>
                </div>
                
                <!-- Name and Edit Button -->
                <div class="ml-6 flex-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {{ displayName }}
                            </h1>
                            <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                                {{ displaySubtitle }}
                            </p>
                        </div>
                        <Link
                            v-if="isOwnProfile"
                            :href="route('profile.edit')"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                        >
                            Edit Profile
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Business Field -->
            <div v-if="profileUser.business_field && !displaySubtitle.includes(profileUser.business_field)" 
                 class="text-gray-600 dark:text-gray-400 mb-4 text-base">
                {{ profileUser.business_field }}
            </div>

            <!-- Verified Mentor Badge -->
            <div v-if="profileUser.is_verified_mentor" class="mb-4">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Verified Mentor
                </span>
            </div>

            <!-- Portfolio and Website Links -->
            <div v-if="profileUser.portfolio_url || profileUser.website_url" class="flex gap-3">
                <a
                    v-if="profileUser.portfolio_url"
                    :href="profileUser.portfolio_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors"
                >
                    Portfolio
                </a>
                <a
                    v-if="profileUser.website_url"
                    :href="profileUser.website_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition-colors"
                >
                    Website
                </a>
            </div>
        </div>
    </div>
</template>

