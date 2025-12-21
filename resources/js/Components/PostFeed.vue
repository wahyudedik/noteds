<script setup>
import { Link, router } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    posts: Object,
    filters: Object,
    userVotes: {
        type: Object,
        default: () => ({}),
    },
});

const postsList = ref([]);
const userVotesList = ref({});
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);

// Initialize posts and votes from props
const initializePosts = () => {
    if (props.posts?.data) {
        postsList.value = [...props.posts.data];
        userVotesList.value = { ...props.userVotes };
        currentPage.value = props.posts.current_page || 1;
        hasMorePages.value = props.posts.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializePosts();
    
    // Setup Intersection Observer after next tick to ensure sentinelRef is mounted
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

// Watch for filter changes and reset
watch(() => [props.filters, props.posts, props.userVotes], () => {
    initializePosts();
}, { deep: true });

const filterByPurpose = (purposeType) => {
    router.get(route('home'), { purpose_type: purposeType || 'all' }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const loadMore = () => {
    if (isLoading.value || !hasMorePages.value) return;
    
    isLoading.value = true;
    
    const nextPage = currentPage.value + 1;
    const queryParams = {
        page: nextPage,
        ...props.filters,
    };
    
    router.get(
        route('home'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['posts', 'userVotes'],
            onSuccess: (page) => {
                const newPosts = page.props.posts?.data || [];
                postsList.value.push(...newPosts);
                
                // Merge userVotes (new votes from new posts)
                if (page.props.userVotes) {
                    userVotesList.value = { ...userVotesList.value, ...page.props.userVotes };
                }
                
                currentPage.value = page.props.posts.current_page || nextPage;
                hasMorePages.value = page.props.posts.next_page_url !== null;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <div>
        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-2">
            <button
                @click="filterByPurpose('all')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    (!filters?.purpose_type || filters.purpose_type === 'all')
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                ]"
            >
                All
            </button>
            <button
                v-for="(label, type) in PURPOSE_TYPE_LABELS"
                :key="type"
                @click="filterByPurpose(type)"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters?.purpose_type === type
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                ]"
            >
                {{ label }}
            </button>
        </div>

        <!-- Posts List -->
        <div class="space-y-4">
            <PostCard
                v-for="post in postsList"
                :key="post.id"
                :post="post"
                :user-vote="userVotesList[post.id] || null"
            />

            <!-- Empty State -->
            <div v-if="postsList.length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    No posts found. Be the first to share!
                </p>
            </div>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading" class="mt-6 text-center py-8">
            <div class="inline-flex items-center space-x-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading more posts...</span>
            </div>
        </div>

        <!-- End of results message -->
        <div v-if="!hasMorePages && postsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
            No more posts to load.
        </div>

        <!-- Sentinel element for infinite scroll -->
        <div ref="sentinelRef" class="h-4"></div>
    </div>
</template>

