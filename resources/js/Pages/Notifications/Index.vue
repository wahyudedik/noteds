<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotificationList from '@/Components/Notifications/NotificationList.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    notifications: Object,
    filters: Object,
});

const notificationsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);
const selectedCategory = ref(props.filters?.category || 'all');

// Initialize notifications list from props
const initializeNotifications = () => {
    if (props.notifications?.data) {
        notificationsList.value = [...props.notifications.data];
        currentPage.value = props.notifications.current_page || 1;
        hasMorePages.value = props.notifications.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeNotifications();
    
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
watch(() => [props.filters, props.notifications], () => {
    initializeNotifications();
}, { deep: true });

const filterByCategory = (category) => {
    selectedCategory.value = category || 'all';
    router.get(route('notifications.index'), { category: category || 'all' }, {
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
        category: selectedCategory.value !== 'all' ? selectedCategory.value : null,
    };
    
    router.get(
        route('notifications.index'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['notifications'],
            onSuccess: (page) => {
                const newNotifications = page.props.notifications?.data || [];
                notificationsList.value.push(...newNotifications);
                currentPage.value = page.props.notifications.current_page || nextPage;
                hasMorePages.value = page.props.notifications.next_page_url !== null;
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
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Notifications
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- Category Filters -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        @click="filterByCategory('all')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'all' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="filterByCategory('payments')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'payments' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Payments
                    </button>
                    <button
                        @click="filterByCategory('orders')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'orders' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Orders
                    </button>
                    <button
                        @click="filterByCategory('withdrawals')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'withdrawals' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Withdrawals
                    </button>
                    <button
                        @click="filterByCategory('products')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'products' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Products
                    </button>
                    <button
                        @click="filterByCategory('campaigns')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'campaigns' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Campaigns
                    </button>
                    <button
                        @click="filterByCategory('clips')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'clips' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Clips
                    </button>
                    <button
                        @click="filterByCategory('brand')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'brand' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Brand
                    </button>
                    <button
                        @click="filterByCategory('support')"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors text-sm',
                            selectedCategory === 'support' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Support
                    </button>
                </div>

                <!-- Notifications List -->
                <NotificationList
                    :notifications="notificationsList"
                    :is-loading="isLoading"
                />

                <!-- End of results message -->
                <div v-if="!hasMorePages && notificationsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                    No more notifications to load.
                </div>

                <!-- Sentinel element for infinite scroll -->
                <div ref="sentinelRef" class="h-4"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

