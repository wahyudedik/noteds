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

const hasBrandProfile = computed(() => {
    return !!props.brandRegistration;
});

const hasClipperProfile = computed(() => {
    return !!props.clipperProfile;
});
</script>

<template>
    <Head :title="(profileUser.business_name || profileUser.name) + ' - Profile'" />

    <AuthenticatedLayout>
        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Profile Header -->
                <ProfileHeader 
                    :profile-user="profileUser"
                    :is-own-profile="isOwnProfile"
                    :is-following="isFollowing"
                />

                <!-- Tabs -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <Tabs 
                        :active-tab="activeTab" 
                        :is-own-profile="isOwnProfile"
                        :profile-user="profileUser"
                        :has-brand-profile="hasBrandProfile"
                        :has-clipper-profile="hasClipperProfile"
                        @update:active-tab="activeTab = $event" 
                    />

                    <!-- Tab Content -->
                    <div class="p-6">
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
                            :profile-user="profileUser"
                        />

                        <TabBrand
                            v-else-if="activeTab === 'brand'"
                            :profile-user="profileUser"
                            :brand-registration="brandRegistration"
                            :is-own-profile="isOwnProfile"
                        />

                        <TabClipper
                            v-else-if="activeTab === 'clipper'"
                            :profile-user="profileUser"
                            :clipper-profile="clipperProfile"
                            :is-own-profile="isOwnProfile"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
