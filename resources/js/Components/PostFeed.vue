<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import PostCard from '@/Components/PostCard.vue';
import SkeletonPostCard from '@/Components/SkeletonPostCard.vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';

const page = usePage();

const props = defineProps({
    posts: Object,
    filters: Object,
    userVotes: {
        type: Object,
        default: () => ({}),
    },
    userBookmarks: {
        type: Object,
        default: () => ({}),
    },
    userReposts: {
        type: Object,
        default: () => ({}),
    },
});

const postsList = ref([]);
const userVotesList = ref({});
const userBookmarksList = ref({});
const userRepostsList = ref({});
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const lastRequestTime = ref(0);
const REQUEST_COOLDOWN = 1000; // Minimum 1 second between requests

// Initialize posts, votes, bookmarks, and reposts from props
const initializePosts = () => {
    if (props.posts?.data) {
        postsList.value = [...props.posts.data];
        userVotesList.value = { ...props.userVotes };
        userBookmarksList.value = { ...props.userBookmarks };
        userRepostsList.value = { ...props.userReposts };
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
                        const now = Date.now();
                        // Prevent requests if less than 1 second has passed since last request
                        if (now - lastRequestTime.value >= REQUEST_COOLDOWN) {
                            loadMore();
                        }
                    }
                },
                {
                    rootMargin: '100px',
                }
            );
            
            observerInstance.value.observe(sentinelRef.value);
        }
    });
    
    // Restore last selected purpose filter from localStorage
    try {
        const saved = localStorage.getItem('feed_purpose_type');
        const current = props.filters?.purpose_type || 'all';
        if (saved && saved !== current) {
            filterByPurpose(saved);
        }
    } catch {}
});

onUnmounted(() => {
    if (observerInstance.value) {
        observerInstance.value.disconnect();
    }
});

// Watch for filter changes and reset
watch(() => [props.filters, props.posts, props.userVotes, props.userBookmarks, props.userReposts], () => {
    initializePosts();
}, { deep: true });

// Get current route name based on URL
const currentRouteName = computed(() => {
    const url = page.url;
    if (url.startsWith('/posts') || url === '/posts') {
        return 'posts.index';
    }
    return 'home';
});

const sortMode = ref((props.filters?.sort || 'latest'));
const period = ref((props.filters?.period || 'week'));
const metric = ref((props.filters?.metric || 'engagement'));
let lastSortMode = sortMode.value;

const filterByPurpose = async (purposeType) => {
    const next = purposeType || 'all';
    try { localStorage.setItem('feed_purpose_type', next); } catch {}
    const baseRoute = sortMode.value === 'top' ? 'posts.top' : currentRouteName.value;
    const params = sortMode.value === 'top'
        ? { purpose_type: next, period: period.value, metric: metric.value }
        : { purpose_type: next };
    try {
        const payload = {
            previous_sort: lastSortMode,
            new_sort: sortMode.value,
            period: period.value,
            metric: metric.value,
            route: baseRoute,
        };
        lastSortMode = sortMode.value;
        if (page.props.auth?.user) {
            await axios.post(route('analytics.events.store'), { type: 'feed_sort_change', payload });
        }
    } catch {}
    router.get(route(baseRoute), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const blockedIds = computed(() => {
    const v = page.props.blocked_user_ids || [];
    return Array.isArray(v) ? v : Object.values(v);
});
const visiblePosts = computed(() => {
    return postsList.value.filter(p => !blockedIds.value.includes(p.user_id));
});

const getCurrentPurpose = () => props.filters?.purpose_type || 'all';
const setSort = (mode) => {
    sortMode.value = mode;
    filterByPurpose(getCurrentPurpose());
};
const setPeriodTab = (p) => {
    period.value = p;
    filterByPurpose(getCurrentPurpose());
};
const setMetricTab = (m) => {
    metric.value = m;
    filterByPurpose(getCurrentPurpose());
};

const loadMore = () => {
    if (isLoading.value || !hasMorePages.value) return;
    
    const now = Date.now();
    // Double-check cooldown before making request
    if (now - lastRequestTime.value < REQUEST_COOLDOWN) {
        return;
    }
    
    isLoading.value = true;
    lastRequestTime.value = now;
    
    const nextPage = currentPage.value + 1;
    const queryParams = {
        page: nextPage,
        ...props.filters,
    };
    
    router.get(
        route(currentRouteName.value),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['posts', 'userVotes', 'userBookmarks', 'userReposts'],
            onSuccess: (page) => {
                const newPosts = page.props.posts?.data || [];
                postsList.value.push(...newPosts);
                
                // Merge userVotes (new votes from new posts)
                if (page.props.userVotes) {
                    userVotesList.value = { ...userVotesList.value, ...page.props.userVotes };
                }
                
                // Merge userBookmarks (new bookmarks from new posts)
                if (page.props.userBookmarks) {
                    userBookmarksList.value = { ...userBookmarksList.value, ...page.props.userBookmarks };
                }
                
                // Merge userReposts (new reposts from new posts)
                if (page.props.userReposts) {
                    userRepostsList.value = { ...userRepostsList.value, ...page.props.userReposts };
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
    <div class="rm-target-feed">
        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-2 items-center">
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
            <div class="w-full mt-2">
                <div class="flex flex-col gap-2">
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-0.5" role="tablist" aria-label="Sort">
                        <button
                            :aria-selected="sortMode==='latest'"
                            role="tab"
                            @click="setSort('latest')"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-md transition-colors',
                                sortMode==='latest' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >Latest</button>
                        <button
                            :aria-selected="sortMode==='top'"
                            role="tab"
                            @click="setSort('top')"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-md transition-colors',
                                sortMode==='top' ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >Top</button>
                    </div>
                    <div v-if="sortMode==='top'" class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-0.5 overflow-x-auto" role="tablist" aria-label="Period">
                        <button
                            v-for="p in ['day','week','month','year','all']"
                            :key="p"
                            :aria-selected="period===p"
                            role="tab"
                            @click="setPeriodTab(p)"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-md transition-colors',
                                period===p ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            {{ p==='day'?'24h':p==='week'?'7d':p==='month'?'30d':p==='year'?'1y':'All-time' }}
                        </button>
                    </div>
                    <div v-if="sortMode==='top'" class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-0.5 overflow-x-auto flex-wrap" role="tablist" aria-label="Metric">
                        <button
                            v-for="m in ['engagement','upvotes','mixed']"
                            :key="m"
                            :aria-selected="metric===m"
                            role="tab"
                            @click="setMetricTab(m)"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-md transition-colors',
                                metric===m ? 'bg-indigo-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            {{ m==='engagement'?'Engagement':m==='upvotes'?'Upvotes':'Mixed' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts List -->
    <div class="space-y-4">
            <div v-if="postsList.length===0 && isLoading" class="space-y-4">
                <SkeletonPostCard />
                <SkeletonPostCard />
                <SkeletonPostCard />
            </div>
            <PostCard
                v-for="post in visiblePosts"
                :key="post.id"
                :post="post"
                :user-vote="userVotesList[post.id] || null"
                :is-bookmarked="userBookmarksList[post.id] || false"
                :is-reposted="userRepostsList[post.id] || false"
            />

            <!-- Empty State -->
            <div v-if="visiblePosts.length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    No posts found. Be the first to share!
                </p>
            </div>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading && postsList.length>0" class="mt-6 text-center py-8">
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

