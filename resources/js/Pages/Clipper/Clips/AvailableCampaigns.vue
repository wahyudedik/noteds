<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    campaigns: Object,
    filters: Object,
});

const campaignsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const searchQuery = ref(props.filters?.search || '');

// Initialize campaigns list from props
const initializeCampaigns = () => {
    if (props.campaigns?.data) {
        campaignsList.value = [...props.campaigns.data];
        currentPage.value = props.campaigns.current_page || 1;
        hasMorePages.value = props.campaigns.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeCampaigns();
    
    // Setup Intersection Observer after next tick
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
watch(() => [props.filters, props.campaigns], () => {
    initializeCampaigns();
}, { deep: true });

const search = () => {
    router.get(route('clipper.campaigns.available'), { search: searchQuery.value }, {
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
        search: searchQuery.value || null,
    };
    
    router.get(
        route('clipper.campaigns.available'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['campaigns'],
            onSuccess: (page) => {
                const newCampaigns = page.props.campaigns?.data || [];
                campaignsList.value.push(...newCampaigns);
                currentPage.value = page.props.campaigns.current_page || nextPage;
                hasMorePages.value = page.props.campaigns.next_page_url !== null;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getYouTubeThumbnail = (url) => {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/);
    if (match) {
        return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
    }
    return null;
};

const getVideoType = (url) => {
    if (!url) return null;
    const youtubePattern = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)/i;
    const drivePattern = /^(https?:\/\/)?(drive|docs)\.google\.com\/(file\/d\/|open\?id=|file\/d\/)/i;
    
    if (youtubePattern.test(url)) return 'youtube';
    if (drivePattern.test(url)) return 'google_drive';
    return null;
};
</script>

<template>
    <Head title="Available Campaigns" />

    <ClipperLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Available Campaigns
                </h2>
                <Link
                    :href="route('clipper.clips.index')"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    My Clips
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="search"
                            type="text"
                            placeholder="Search campaigns..."
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <button
                            @click="search"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <!-- Campaigns Grid -->
                <div v-if="campaignsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="campaign in campaignsList"
                        :key="campaign.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {{ campaign.title }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                {{ campaign.description }}
                            </p>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">CPM:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(campaign.cpm) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Budget:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(campaign.max_budget) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Duration:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ campaign.duration_days }} days
                                    </span>
                                </div>
                                <div v-if="campaign.max_reward_per_clipper" class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Max Reward:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(campaign.max_reward_per_clipper) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Video References -->
                            <div v-if="campaign.video_references && campaign.video_references.length > 0" class="mb-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Video References:
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(videoRef, index) in campaign.video_references.slice(0, 2)"
                                        :key="index"
                                        class="flex items-center gap-2"
                                    >
                                        <a
                                            :href="videoRef.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                        >
                                            <span v-if="getVideoType(videoRef.url) === 'youtube'" class="text-red-600">▶</span>
                                            <span v-else-if="getVideoType(videoRef.url) === 'google_drive'" class="text-blue-600">📁</span>
                                            <span class="truncate">
                                                {{ videoRef.title || `Video ${index + 1}` }}
                                            </span>
                                        </a>
                                    </div>
                                    <div v-if="campaign.video_references.length > 2" class="text-xs text-gray-500 dark:text-gray-400">
                                        + {{ campaign.video_references.length - 2 }} more video(s)
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link
                                    :href="route('clipper.clips.create', campaign.id)"
                                    class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Submit Clip
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No available campaigns found.</p>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more campaigns...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && campaignsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more campaigns to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </ClipperLayout>
</template>

