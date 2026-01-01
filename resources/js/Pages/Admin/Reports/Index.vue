<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    reports: Object,
    filters: Object,
});

const reportsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const selectedStatus = ref(props.filters?.status || 'all');
const selectedType = ref(props.filters?.type || 'all');

// Initialize reports list from props
const initializeReports = () => {
    if (props.reports?.data) {
        reportsList.value = [...props.reports.data];
        currentPage.value = props.reports.current_page || 1;
        hasMorePages.value = props.reports.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeReports();
    
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
watch(() => [props.filters, props.reports], () => {
    initializeReports();
}, { deep: true });

const filterByStatus = (status) => {
    selectedStatus.value = status || 'all';
    router.get(route('admin.reports.index'), {
        status: status || 'all',
        type: selectedType.value !== 'all' ? selectedType.value : null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByType = (type) => {
    selectedType.value = type || 'all';
    router.get(route('admin.reports.index'), {
        status: selectedStatus.value !== 'all' ? selectedStatus.value : null,
        type: type || 'all',
    }, {
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
        type: selectedType.value !== 'all' ? selectedType.value : null,
    };
    
    router.get(
        route('admin.reports.index'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['reports'],
            onSuccess: (page) => {
                const newReports = page.props.reports?.data || [];
                reportsList.value.push(...newReports);
                currentPage.value = page.props.reports.current_page || nextPage;
                hasMorePages.value = page.props.reports.next_page_url !== null;
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
        reviewing: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        dismissed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return classes[status] || classes.pending;
};

const getReasonLabel = (reason) => {
    const labels = {
        spam: 'Spam',
        harassment: 'Harassment',
        inappropriate: 'Inappropriate',
        copyright: 'Copyright',
        fake: 'Fake',
        other: 'Other',
    };
    return labels[reason] || reason;
};

const getTypeLabel = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Content Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Content Reports
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-6 space-y-4">
                    <!-- Status Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByStatus('all')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'all' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="filterByStatus('pending')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'pending' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Pending
                            </button>
                            <button
                                @click="filterByStatus('reviewing')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'reviewing' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Reviewing
                            </button>
                            <button
                                @click="filterByStatus('resolved')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'resolved' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Resolved
                            </button>
                            <button
                                @click="filterByStatus('dismissed')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedStatus === 'dismissed' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Dismissed
                            </button>
                        </div>
                    </div>

                    <!-- Type Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="filterByType('all')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'all' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                All
                            </button>
                            <button
                                @click="filterByType('post')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'post' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Posts
                            </button>
                            <button
                                @click="filterByType('comment')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'comment' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Comments
                            </button>
                            <button
                                @click="filterByType('user')"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors text-sm',
                                    selectedType === 'user' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                Users
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reports List -->
                <div v-if="reportsList.length > 0" class="space-y-4">
                    <div
                        v-for="report in reportsList"
                        :key="report.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <Link
                                        :href="route('admin.reports.show', report.id)"
                                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                                    >
                                        Report #{{ report.id.slice(0, 8) }}
                                    </Link>
                                    <span
                                        :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(report.status)]"
                                    >
                                        {{ report.status }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ getTypeLabel(report.reportable_type) }}
                                    </span>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Reason</div>
                                        <div class="text-base font-medium text-gray-900 dark:text-white capitalize">
                                            {{ getReasonLabel(report.reason) }}
                                        </div>
                                    </div>
                                    <div v-if="report.notes">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Notes</div>
                                        <div class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                            {{ report.notes }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Reported by</div>
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ report.user?.name || report.user?.email || 'Unknown' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Reported: {{ formatDate(report.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('admin.reports.show', report.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Review
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">No reports found.</p>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more reports...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && reportsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more reports to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

