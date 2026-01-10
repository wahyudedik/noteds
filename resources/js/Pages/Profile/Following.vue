<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FollowButton from '@/Components/Follow/FollowButton.vue';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    following: Object,
    isFollowing: {
        type: Boolean,
        default: false,
    },
    mutualConnectionsMap: {
        type: Object,
        default: () => ({}),
    },
});

const followingList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);

// Track following status for each user
const followingStatus = ref({});

const initializeFollowing = () => {
    if (props.following?.data) {
        followingList.value = [...props.following.data];
        currentPage.value = props.following.current_page || 1;
        hasMorePages.value = props.following.next_page_url !== null;
        
        // Initialize following status (all are following since this is the following list)
        props.following.data.forEach(follow => {
            followingStatus.value[follow.following.id] = true;
        });
    }
};

onMounted(() => {
    initializeFollowing();
    
    nextTick(() => {
        if (sentinelRef.value) {
            observerInstance.value = new IntersectionObserver(
                (entries) => {
                    if (entries[0].isIntersecting && hasMorePages.value && !isLoading.value) {
                        loadMore();
                    }
                },
                {
                    rootMargin: '100px',
                }
            );
            
            observerInstance.value.observe(sentinelRef.value);
        }
    });
});

onUnmounted(() => {
    if (observerInstance.value) {
        observerInstance.value.disconnect();
    }
});

const loadMore = () => {
    if (isLoading.value || !hasMorePages.value) return;
    
    isLoading.value = true;
    
    const nextPage = currentPage.value + 1;
    
    router.get(
        route('users.following', props.user.id),
        { page: nextPage },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['following'],
            onSuccess: (page) => {
                const newFollowing = page.props.following?.data || [];
                followingList.value.push(...newFollowing);
                currentPage.value = page.props.following.current_page || nextPage;
                hasMorePages.value = page.props.following.next_page_url !== null;
                
                // Update following status
                newFollowing.forEach(follow => {
                    followingStatus.value[follow.following.id] = true;
                });
                
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};

const handleFollowed = (userId) => {
    followingStatus.value[userId] = true;
};

const handleUnfollowed = (userId) => {
    followingStatus.value[userId] = false;
};
</script>

<template>
    <Head :title="(user.business_name || user.name) + ' - Following'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Following
                </h2>
                <Link
                    :href="route('profile.show', user.id)"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    ← Back to Profile
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- User Info -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <Link
                        :href="route('profile.show', user.id)"
                        class="flex items-center gap-3"
                    >
                        <div class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-lg overflow-hidden flex-shrink-0">
                            <img
                                v-if="user.avatar_url"
                                :src="user.avatar_url"
                                :alt="user.business_name || user.name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>
                                {{ (user.business_name || user.name).charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ user.business_name || user.name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Following {{ followingList.length }} {{ followingList.length === 1 ? 'user' : 'users' }}
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Following List -->
                <div v-if="followingList.length > 0" class="space-y-3">
                    <div
                        v-for="follow in followingList"
                        :key="follow.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-center justify-between">
                            <Link
                                :href="route('profile.show', follow.following.id)"
                                class="flex items-center gap-3 flex-1"
                            >
                                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold flex-shrink-0 overflow-hidden">
                                    <img
                                        v-if="follow.following.avatar_url"
                                        :src="follow.following.avatar_url"
                                        :alt="follow.following.business_name || follow.following.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else>
                                        {{ (follow.following.business_name || follow.following.name).charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ follow.following.business_name || follow.following.name }}
                                        </div>
                                        <span
                                            v-if="mutualConnectionsMap[follow.following.id] > 0"
                                            class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 px-1.5 py-0.5 rounded"
                                            :title="`${mutualConnectionsMap[follow.following.id]} mutual ${mutualConnectionsMap[follow.following.id] === 1 ? 'connection' : 'connections'}`"
                                        >
                                            {{ mutualConnectionsMap[follow.following.id] }} mutual
                                        </span>
                                    </div>
                                    <div v-if="follow.following.business_field" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ follow.following.business_field }}
                                    </div>
                                </div>
                            </Link>
                            <div class="ml-4">
                                <FollowButton
                                    :user-id="follow.following.id"
                                    :is-following="followingStatus[follow.following.id] || false"
                                    size="sm"
                                    @followed="handleFollowed(follow.following.id)"
                                    @unfollowed="handleUnfollowed(follow.following.id)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">Not following anyone yet.</p>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && followingList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more users to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

