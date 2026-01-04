<script setup>
import { ref, computed } from 'vue';
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import ProfileHeader from '@/Components/Profile/ProfileHeader.vue';
import ClipperProfileForm from '@/Components/Clipper/ProfileForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    profileUser: {
        type: Object,
        required: false,
        default: () => null,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
    clipperProfile: {
        type: Object,
        default: null,
    },
});

// Safe computed for profileUser to handle undefined/null
const safeProfileUser = computed(() => {
    return props.profileUser || {};
});

const isEditing = ref(false);

const startEdit = () => {
    isEditing.value = true;
};

const cancelEdit = () => {
    isEditing.value = false;
};

const onProfileUpdated = () => {
    isEditing.value = false;
};
</script>

<template>
    <Head :title="(safeProfileUser?.business_name || safeProfileUser?.name || 'Clipper') + ' - Clipper Profile'" />

    <ClipperLayout>
        <div v-if="safeProfileUser && Object.keys(safeProfileUser).length > 0" class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Profile Header -->
                <ProfileHeader 
                    :profile-user="safeProfileUser"
                    :is-own-profile="isOwnProfile"
                />

                <!-- Clipper Profile Content -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div v-if="!isEditing" class="space-y-6">
                            <!-- Header with Edit Button -->
                            <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    Clipper Profile
                                </h2>
                                <button
                                    v-if="isOwnProfile"
                                    @click="startEdit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors"
                                >
                                    Edit Profile
                                </button>
                            </div>

                            <!-- Profile Info -->
                            <div v-if="clipperProfile" class="space-y-6">
                                <!-- Primary Platform -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                        Primary Platform
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Platform</div>
                                            <div class="text-lg font-medium text-gray-900 dark:text-white capitalize">
                                                {{ clipperProfile.platform }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Username</div>
                                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ clipperProfile.platform_username }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Followers</div>
                                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ new Intl.NumberFormat('id-ID').format(clipperProfile.follower_count || 0) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bio -->
                                <div v-if="clipperProfile.bio">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                        About
                                    </h3>
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                        {{ clipperProfile.bio }}
                                    </p>
                                </div>

                                <!-- Portfolio URL -->
                                <div v-if="clipperProfile.portfolio_url">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                        Portfolio
                                    </h3>
                                    <a
                                        :href="clipperProfile.portfolio_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        {{ clipperProfile.portfolio_url }}
                                    </a>
                                </div>

                                <!-- Portfolio Items -->
                                <div v-if="clipperProfile.portfolio_items && clipperProfile.portfolio_items.length > 0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                        Portfolio Items
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div
                                            v-for="(item, index) in clipperProfile.portfolio_items"
                                            :key="index"
                                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4"
                                        >
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded capitalize">
                                                    {{ item.platform }}
                                                </span>
                                            </div>
                                            <a
                                                :href="item.url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all block mb-1"
                                            >
                                                {{ item.url }}
                                            </a>
                                            <p v-if="item.description" class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ item.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400 mb-4">
                                    {{ isOwnProfile ? 'You haven\'t set up your clipper profile yet.' : 'This user hasn\'t set up their clipper profile yet.' }}
                                </p>
                                <button
                                    v-if="isOwnProfile"
                                    @click="startEdit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                                >
                                    Setup Profile
                                </button>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <ClipperProfileForm
                            v-else
                            :clipper-profile="clipperProfile"
                            @cancel="cancelEdit"
                            @updated="onProfileUpdated"
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
    </ClipperLayout>
</template>

