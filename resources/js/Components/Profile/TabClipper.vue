<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
    clipperProfile: {
        type: Object,
        default: null,
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="space-y-6">
        <div v-if="clipperProfile" class="space-y-6">
            <!-- Primary Platform -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    About
                </h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ clipperProfile.bio }}
                </p>
            </div>

            <!-- Portfolio URL -->
            <div v-if="clipperProfile.portfolio_url">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
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

            <!-- Actions for own profile -->
            <div v-if="isOwnProfile" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <Link
                    :href="route('clipper.clips.index')"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    View My Clips
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ isOwnProfile ? 'You haven\'t set up your clipper profile yet.' : 'This user hasn\'t set up their clipper profile yet.' }}
            </p>
            <Link
                v-if="isOwnProfile"
                :href="route('clipper.profile.create')"
                class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Setup Clipper Profile
            </Link>
        </div>
    </div>
</template>

