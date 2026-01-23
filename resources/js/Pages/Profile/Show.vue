<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProfileHeader from '@/Components/Profile/ProfileHeader.vue';
import Tabs from '@/Components/Profile/Tabs.vue';
import TabPosts from '@/Components/Profile/TabPosts.vue';
import TabAnalytics from '@/Components/Profile/TabAnalytics.vue';
import TabAbout from '@/Components/Profile/TabAbout.vue';
import TabBrand from '@/Components/Profile/TabBrand.vue';
import TabClipper from '@/Components/Profile/TabClipper.vue';
import { usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
    posts: {
        type: Array,
        default: () => [],
    },
    userVotes: {
        type: Object,
        default: () => ({}),
    },
    userBookmarks: {
        type: Object,
        default: () => ({}),
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
    stats: Object,
    engagement_data: Object,
    top_posts: Array,
    brandRegistration: {
        type: Object,
        default: null,
    },
    clipperProfile: {
        type: Object,
        default: null,
    },
});

const activeTab = ref('posts');

// Safe computed for profileUser to handle undefined/null
const safeProfileUser = computed(() => {
    return props.profileUser || {};
});

const hasBrandProfile = computed(() => {
    return !!props.brandRegistration;
});

const hasClipperProfile = computed(() => {
    return !!props.clipperProfile;
});
const page = usePage();
const isBlockedByMe = computed(() => {
    const ids = page.props.blocked_user_ids || [];
    const targetId = safeProfileUser.value?.id;
    return !!targetId && (Array.isArray(ids) ? ids.includes(targetId) : Object.values(ids).includes(targetId));
});
</script>

<template>
    <Head :title="(safeProfileUser?.business_name || safeProfileUser?.name || 'Profile') + ' - Profile'" />

    <AuthenticatedLayout>
        <div v-if="safeProfileUser && Object.keys(safeProfileUser).length > 0" class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div v-if="isBlockedByMe" class="mb-3 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-yellow-800">
                    You blocked this user. Unblock dari Settings → Safety untuk melihat semua konten.
                </div>
                <!-- Profile Header -->
                <ProfileHeader 
                    :profile-user="safeProfileUser"
                    :is-own-profile="isOwnProfile"
                    :is-following="isFollowing"
                    :mutual-connections-count="mutualConnectionsCount"
                    :mutual-connections="mutualConnections"
                />

                <!-- Tabs -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <Tabs 
                        :active-tab="activeTab" 
                        :is-own-profile="isOwnProfile"
                        :profile-user="safeProfileUser"
                        :has-brand-profile="hasBrandProfile"
                        :has-clipper-profile="hasClipperProfile"
                        @update:active-tab="activeTab = $event" 
                    />

                    <!-- Tab Content -->
                    <div class="p-4 sm:p-6">
                        <TabPosts 
                            v-if="activeTab === 'posts'"
                            :posts="posts"
                            :user-votes="userVotes"
                            :user-bookmarks="userBookmarks"
                        />

                        <TabAnalytics 
                            v-else-if="activeTab === 'analytics' && isOwnProfile"
                            :stats="stats"
                            :engagement-data="engagement_data"
                            :top-posts="top_posts"
                        />

                        <div v-else-if="activeTab === 'analytics' && !isOwnProfile" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            Analytics hanya tersedia untuk profile Anda sendiri.
                        </div>

                        <TabAbout 
                            v-else-if="activeTab === 'about'"
                            :profile-user="safeProfileUser"
                        />

                        <TabBrand
                            v-else-if="activeTab === 'brand'"
                            :profile-user="safeProfileUser"
                            :brand-registration="brandRegistration"
                            :is-own-profile="isOwnProfile"
                        />

                        <TabClipper
                            v-else-if="activeTab === 'clipper'"
                            :profile-user="safeProfileUser"
                            :clipper-profile="clipperProfile"
                            :is-own-profile="isOwnProfile"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    User not found or profile is loading...
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
