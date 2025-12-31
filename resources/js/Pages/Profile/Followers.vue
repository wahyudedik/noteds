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
    followers: Object,
    isFollowing: {
        type: Boolean,
        default: false,
    },
});

const followersList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);

// Track following status for each follower
const followingStatus = ref({});

const initializeFollowers = () => {
    if (props.followers?.data) {
        followersList.value = [...props.followers.data];
        currentPage.value = props.followers.current_page || 1;
        hasMorePages.value = props.followers.next_page_url !== null;
        
        // Initialize following status
        props.followers.data.forEach(follower => {
            followingStatus.value[follower.follower.id] = follower.follower.is_following || false;
        });
    }
};

onMounted(() => {
    initializeFollowers();
    
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
        route('users.followers', props.user.id),
        { page: nextPage },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['followers'],
            onSuccess: (page) => {
                const newFollowers = page.props.followers?.data || [];
                followersList.value.push(...newFollowers);
                currentPage.value = page.props.followers.current_page || nextPage;
                hasMorePages.value = page.props.followers.next_page_url !== null;
                
                // Update following status
                newFollowers.forEach(follower => {
                    followingStatus.value[follower.follower.id] = follower.follower.is_following || false;
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
    <Head :title="(user.business_name || user.name) + ' - Followers'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Followers
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
                        <div class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-lg">
                            {{ (user.business_name || user.name).charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ user.business_name || user.name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ followersList.length }} {{ followersList.length === 1 ? 'follower' : 'followers' }}
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Followers List -->
                <div v-if="followersList.length > 0" class="space-y-3">
                    <div
                        v-for="follow in followersList"
                        :key="follow.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-center justify-between">
                            <Link
                                :href="route('profile.show', follow.follower.id)"
                                class="flex items-center gap-3 flex-1"
                            >
                                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ (follow.follower.business_name || follow.follower.name).charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white truncate">
                                        {{ follow.follower.business_name || follow.follower.name }}
                                    </div>
                                    <div v-if="follow.follower.business_field" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ follow.follower.business_field }}
                                    </div>
                                </div>
                            </Link>
                            <div class="ml-4">
                                <FollowButton
                                    :user-id="follow.follower.id"
                                    :is-following="followingStatus[follow.follower.id] || false"
                                    size="sm"
                                    @followed="handleFollowed(follow.follower.id)"
                                    @unfollowed="handleUnfollowed(follow.follower.id)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">No followers yet.</p>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more followers...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && followersList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more followers to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

