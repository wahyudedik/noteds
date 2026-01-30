<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FollowButton from '@/Components/Follow/FollowButton.vue';
import MutualConnections from '@/Components/MutualConnections.vue';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
    isFollowing: {
        type: Boolean,
        default: false,
    },
    mutualConnectionsCount: {
        type: Number,
        default: 0,
    },
    mutualConnections: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const displayName = computed(() => props.profileUser.business_name || props.profileUser.name);
const displaySubtitle = computed(() => {
    if (props.profileUser.business_name && props.profileUser.name) {
        return props.profileUser.name;
    }
    return props.profileUser.business_field || '';
});

const avatarInitial = computed(() => displayName.value.charAt(0).toUpperCase());
const avatarUrl = computed(() => props.profileUser.avatar_url || null);
const verifications = computed(() => page.props.verifications || []);
const headerImage = computed(() => props.profileUser.header_image_url || props.profileUser.cover_url || null);
const privacy = computed(() => page.props.privacy || {});
const canMessage = computed(() => {
    if (props.isOwnProfile) return false;
    const perm = privacy.value?.messaging_permission || 'everyone';
    if (perm === 'none') return false;
    if (perm === 'followers') return !!props.isFollowing;
    return true;
});
</script>

<template>
    <div class="bg-white/70 dark:bg-gray-900/60 backdrop-blur rounded-2xl shadow-md ring-1 ring-gray-200/60 dark:ring-gray-700/60 overflow-hidden">
        <!-- Gradient Header -->
        <div class="relative h-32 sm:h-40">
            <img v-if="headerImage" :src="headerImage" alt="" class="absolute inset-0 w-full h-full object-cover" />
            <div :class="headerImage ? 'absolute inset-0 bg-gradient-to-t from-black/40 to-transparent' : 'absolute inset-0 bg-gradient-to-r from-indigo-500 via-fuchsia-600 to-violet-700'"></div>
            <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <!-- Profile Info -->
        <div class="px-4 sm:px-6 pt-2 pb-6 sm:pb-8 relative">
            <!-- Avatar (overlapping header) -->
            <div class="flex flex-col sm:flex-row items-center sm:items-end -mt-16 sm:-mt-24 mb-4">
                <div class="relative mb-3 sm:mb-0">
                    <div 
                        class="h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-indigo-600 ring-4 ring-white/80 dark:ring-gray-800 shadow-lg flex items-center justify-center text-white text-2xl sm:text-4xl font-bold overflow-hidden"
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
                <div class="sm:ml-6 flex-1 w-full sm:w-auto text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1">
                            <h1 class="mt-2 sm:mt-3 text-[1.375rem] sm:text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 mb-2">
                                {{ displayName }}
                            </h1>
                            <p class="text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400 mb-2">
                                {{ displaySubtitle }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center sm:justify-end gap-2">
                            <FollowButton
                                v-if="!isOwnProfile && page.props.auth?.user"
                                :user-id="profileUser.id"
                                :is-following="isFollowing"
                                :can-follow="!!page.props.auth?.user"
                                size="md"
                            />
                            <!-- Messaging removed -->
                            <Link
                                v-if="isOwnProfile"
                                :href="route('profile.edit')"
                                class="px-4 sm:px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs sm:text-sm font-medium transition-all shadow-sm whitespace-nowrap"
                            >
                                Edit Profile
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Field -->
            <div v-if="profileUser.business_field && !displaySubtitle.includes(profileUser.business_field)" 
                 class="text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 text-sm sm:text-base text-center sm:text-left">
                {{ profileUser.business_field }}
            </div>

            <!-- Verified Mentor Badge -->
            <div v-if="profileUser.is_verified_mentor" class="mb-3 sm:mb-4 flex justify-center sm:justify-start">
                <span class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 shadow-sm">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Verified Mentor
                </span>
            </div>
            <!-- Verification Badges -->
            <div v-if="verifications.length" class="mb-3 sm:mb-4 flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                <span v-for="v in verifications" :key="v.slug" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 shadow-sm">
                    <span v-if="v.icon">{{ v.icon }}</span>
                    <span class="capitalize">{{ v.type }}</span>
                </span>
            </div>

            <!-- Mutual Connections (only when viewing other user's profile) -->
            <div v-if="!isOwnProfile && mutualConnectionsCount > 0" class="mb-3 sm:mb-4">
                <MutualConnections
                    :connections="mutualConnections"
                    :count="mutualConnectionsCount"
                    :target-user-id="profileUser.id"
                />
            </div>

            <!-- Portfolio and Website Links -->
            <div v-if="profileUser.portfolio_url || profileUser.website_url" class="flex flex-col sm:flex-row gap-2 sm:gap-3 justify-center sm:justify-start">
                <a
                    v-if="profileUser.portfolio_url"
                    :href="profileUser.portfolio_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm"
                >
                    Portfolio
                </a>
                <a
                    v-if="profileUser.website_url"
                    :href="profileUser.website_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs sm:text-sm font-medium transition-colors shadow-sm"
                >
                    Website
                </a>
            </div>
        </div>
    </div>
</template>
