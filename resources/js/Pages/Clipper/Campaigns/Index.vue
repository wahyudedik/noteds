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
const selectedStatus = ref(props.filters?.status || 'all');

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

const filterByStatus = (status) => {
    selectedStatus.value = status || 'all';
    router.get(route('clipper.campaigns.index'), { status: status || 'all' }, {
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
        status: selectedStatus.value !== 'all' ? selectedStatus.value : null,
    };
    
    router.get(
        route('clipper.campaigns.index'),
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

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.draft;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <Head title="My Campaigns" />

    <ClipperLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h2 class="text-lg sm:text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    My Campaigns
                </h2>
                <Link
                    :href="route('clipper.campaigns.create')"
                    class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center min-h-[44px] flex items-center justify-center"
                >
                    Create Campaign
                </Link>
            </div>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Status Filters -->
                <div class="mb-4 sm:mb-6 flex flex-wrap gap-2 overflow-x-auto pb-2">
                    <button
                        @click="filterByStatus('all')"
                        :class="[
                            'px-3 sm:px-4 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium whitespace-nowrap min-h-[44px]',
                            selectedStatus === 'all' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('draft')"
                        :class="[
                            'px-3 sm:px-4 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium whitespace-nowrap min-h-[44px]',
                            selectedStatus === 'draft' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Draft
                    </button>
                    <button
                        @click="filterByStatus('active')"
                        :class="[
                            'px-3 sm:px-4 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium whitespace-nowrap min-h-[44px]',
                            selectedStatus === 'active' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Active
                    </button>
                    <button
                        @click="filterByStatus('completed')"
                        :class="[
                            'px-3 sm:px-4 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium whitespace-nowrap min-h-[44px]',
                            selectedStatus === 'completed' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Completed
                    </button>
                    <button
                        @click="filterByStatus('cancelled')"
                        :class="[
                            'px-3 sm:px-4 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium whitespace-nowrap min-h-[44px]',
                            selectedStatus === 'cancelled' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Cancelled
                    </button>
                </div>

                <!-- Campaigns List -->
                <div v-if="campaignsList.length > 0" class="space-y-3 sm:space-y-4">
                    <div
                        v-for="campaign in campaignsList"
                        :key="campaign.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2">
                                    <Link
                                        :href="route('clipper.campaigns.show', campaign.id)"
                                        class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2"
                                    >
                                        {{ campaign.title }}
                                    </Link>
                                    <span
                                        :class="['px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0', getStatusBadgeClass(campaign.status)]"
                                    >
                                        {{ campaign.status }}
                                    </span>
                                </div>
                                
                                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 line-clamp-2">
                                    {{ campaign.description }}
                                </p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4">
                                    <div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Budget</div>
                                        <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white break-words">
                                            Rp {{ formatCurrency(campaign.max_budget) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">CPM</div>
                                        <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                            Rp {{ formatCurrency(campaign.cpm) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                                        <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ formatCurrency(campaign.total_views) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Clips</div>
                                        <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ campaign.total_clips || 0 }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="campaign.campaign_wallet" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center text-xs sm:text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Remaining Budget:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white break-words text-right ml-2">
                                            Rp {{ formatCurrency(campaign.campaign_wallet.remaining_budget) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs sm:text-sm mt-1">
                                        <span class="text-gray-500 dark:text-gray-400">Total Spent:</span>
                                        <span class="font-semibold text-gray-900 dark:text-white break-words text-right ml-2">
                                            Rp {{ formatCurrency(campaign.total_spent) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row gap-2">
                            <Link
                                :href="route('clipper.campaigns.show', campaign.id)"
                                class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center min-h-[44px] flex items-center justify-center"
                            >
                                View Details
                            </Link>
                            <Link
                                :href="route('clipper.campaigns.analytics.show', campaign.id)"
                                class="px-3 sm:px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base font-medium text-center min-h-[44px] flex items-center justify-center"
                            >
                                View Analytics
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 sm:py-12">
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mb-4">No campaigns found.</p>
                    <Link
                        :href="route('clipper.campaigns.create')"
                        class="inline-block px-4 sm:px-6 py-2 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium min-h-[44px] flex items-center justify-center"
                    >
                        Create Your First Campaign
                    </Link>
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

