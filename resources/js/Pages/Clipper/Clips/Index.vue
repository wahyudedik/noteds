<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    clips: Object,
    filters: Object,
});

const clipsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const selectedStatus = ref(props.filters?.status || 'all');

// Initialize clips list from props
const initializeClips = () => {
    if (props.clips?.data) {
        clipsList.value = [...props.clips.data];
        currentPage.value = props.clips.current_page || 1;
        hasMorePages.value = props.clips.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeClips();
    
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
watch(() => [props.filters, props.clips], () => {
    initializeClips();
}, { deep: true });

const filterByStatus = (status) => {
    selectedStatus.value = status || 'all';
    router.get(route('clipper.clips.index'), { status: status || 'all' }, {
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
        route('clipper.clips.index'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['clips'],
            onSuccess: (page) => {
                const newClips = page.props.clips?.data || [];
                clipsList.value.push(...newClips);
                currentPage.value = page.props.clips.current_page || nextPage;
                hasMorePages.value = page.props.clips.next_page_url !== null;
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
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[status] || classes.pending;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <Head title="My Clips" />

    <ClipperLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    My Clips
                </h2>
                <Link
                    :href="route('clipper.campaigns.available')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Submit New Clip
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Status Filters -->
                <div class="mb-6 flex space-x-2">
                    <button
                        @click="filterByStatus('all')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            selectedStatus === 'all' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('pending')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            selectedStatus === 'pending' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Pending
                    </button>
                    <button
                        @click="filterByStatus('approved')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            selectedStatus === 'approved' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Approved
                    </button>
                    <button
                        @click="filterByStatus('paid')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            selectedStatus === 'paid' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Paid
                    </button>
                    <button
                        @click="filterByStatus('rejected')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            selectedStatus === 'rejected' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Rejected
                    </button>
                </div>

                <!-- Clips List -->
                <div v-if="clipsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="clip in clipsList"
                        :key="clip.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <Link :href="route('clipper.clips.show', clip.id)">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(clip.status)]"
                                    >
                                        {{ clip.status }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ new Date(clip.submitted_at).toLocaleDateString() }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ clip.campaign?.title || 'Campaign' }}
                                </h3>

                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Platform:</span>
                                        <span class="font-medium text-gray-900 dark:text-white capitalize">
                                            {{ clip.platform }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Views:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(clip.valid_views) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Reward:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400">
                                            Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Click to view details
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No clips found.</p>
                    <Link
                        :href="route('clipper.campaigns.available')"
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Submit Your First Clip
                    </Link>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more clips...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && clipsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more clips to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </ClipperLayout>
</template>

