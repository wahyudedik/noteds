<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    registrations: Object,
    filters: Object,
});

const registrationsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const selectedStatus = ref(props.filters?.status || 'all');

// Initialize registrations list from props
const initializeRegistrations = () => {
    if (props.registrations?.data) {
        registrationsList.value = [...props.registrations.data];
        currentPage.value = props.registrations.current_page || 1;
        hasMorePages.value = props.registrations.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeRegistrations();
    
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
watch(() => [props.filters, props.registrations], () => {
    initializeRegistrations();
}, { deep: true });

const filterByStatus = (status) => {
    selectedStatus.value = status || 'all';
    router.get(route('admin.brand-approvals.index'), { status: status || 'all' }, {
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
        route('admin.brand-approvals.index'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['registrations'],
            onSuccess: (page) => {
                const newRegistrations = page.props.registrations?.data || [];
                registrationsList.value.push(...newRegistrations);
                currentPage.value = page.props.registrations.current_page || nextPage;
                hasMorePages.value = page.props.registrations.next_page_url !== null;
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
    };
    return classes[status] || classes.pending;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Brand Approvals" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Brand Approvals
            </h2>
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

                <!-- Registrations List -->
                <div v-if="registrationsList.length > 0" class="space-y-4">
                    <div
                        v-for="registration in registrationsList"
                        :key="registration.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <Link
                                        :href="route('admin.brand-approvals.show', registration.id)"
                                        class="text-xl font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ registration.company_name }}
                                    </Link>
                                    <span
                                        :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(registration.status)]"
                                    >
                                        {{ registration.status }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Business Type</div>
                                        <div class="text-base font-medium text-gray-900 dark:text-white capitalize">
                                            {{ registration.business_type }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Contact</div>
                                        <div class="text-base font-medium text-gray-900 dark:text-white">
                                            {{ registration.contact_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ registration.contact_email }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Submitted: {{ formatDate(registration.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('admin.brand-approvals.show', registration.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                            >
                                Review
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No brand registrations found.</p>
                </div>

                <!-- Loading indicator -->
                <div v-if="isLoading" class="mt-6 text-center py-8">
                    <div class="inline-flex items-center space-x-2 text-gray-500">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading more registrations...</span>
                    </div>
                </div>

                <!-- End of results message -->
                <div v-if="!hasMorePages && registrationsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more registrations to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

