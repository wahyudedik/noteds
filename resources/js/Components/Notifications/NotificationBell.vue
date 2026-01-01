<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        default: () => [],
    },
});

const isOpen = ref(false);
const dropdownRef = ref(null);

const notificationsList = computed(() => {
    // Handle pagination object - extract data array if it's a pagination object
    if (props.notifications && typeof props.notifications === 'object' && 'data' in props.notifications) {
        return props.notifications.data || [];
    }
    // Return as-is if it's already an array
    return Array.isArray(props.notifications) ? props.notifications : [];
});

const unreadCount = computed(() => {
    return notificationsList.value.filter(n => !n.read_at).length;
});

const recentNotifications = computed(() => {
    return notificationsList.value.slice(0, 5);
});

const getNotificationType = (notification) => {
    // Try to get type from data field first (new format)
    if (notification.data?.type) {
        return notification.data.type;
    }
    // Fallback to extracting from notification type (class name)
    const type = notification.type || '';
    if (type.includes('NewComment')) return 'new_comment';
    if (type.includes('NewFollow')) return 'new_follow';
    if (type.includes('ContentReported')) return 'content_reported';
    if (type.includes('ReportResolved')) return 'report_resolved';
    if (type.includes('NewCampaign')) return 'new_campaign';
    if (type.includes('ClipApproved')) return 'clip_approved';
    if (type.includes('RewardReceived')) return 'reward_received';
    if (type.includes('CampaignEnded')) return 'campaign_ended';
    if (type.includes('BrandApproved')) return 'brand_approved';
    if (type.includes('BrandRejected')) return 'brand_rejected';
    if (type.includes('NewOrder')) return 'new_order';
    if (type.includes('WithdrawalStatus')) return 'withdrawal_status';
    return 'default';
};

const getNotificationIcon = (notification) => {
    const type = getNotificationType(notification);
    const icons = {
        'new_comment': 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'new_follow': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'content_reported': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'report_resolved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'new_campaign': 'M12 4v16m8-8H4',
        'clip_approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'reward_received': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'campaign_ended': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'brand_approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'brand_rejected': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'new_order': 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'withdrawal_status': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
    };
    return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
};

const getNotificationColor = (notification) => {
    const type = getNotificationType(notification);
    const colors = {
        'new_comment': 'text-blue-600 dark:text-blue-400',
        'new_follow': 'text-green-600 dark:text-green-400',
        'content_reported': 'text-red-600 dark:text-red-400',
        'report_resolved': 'text-green-600 dark:text-green-400',
        'new_campaign': 'text-blue-600 dark:text-blue-400',
        'clip_approved': 'text-green-600 dark:text-green-400',
        'reward_received': 'text-yellow-600 dark:text-yellow-400',
        'campaign_ended': 'text-gray-600 dark:text-gray-400',
        'brand_approved': 'text-green-600 dark:text-green-400',
        'brand_rejected': 'text-red-600 dark:text-red-400',
        'new_order': 'text-indigo-600 dark:text-indigo-400',
        'withdrawal_status': 'text-purple-600 dark:text-purple-400',
    };
    return colors[type] || 'text-gray-600 dark:text-gray-400';
};

const formatDate = (date) => {
    const now = new Date();
    const notificationDate = new Date(date);
    const diffInSeconds = Math.floor((now - notificationDate) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    
    return notificationDate.toLocaleDateString('id-ID', {
        month: 'short',
        day: 'numeric',
    });
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const markAsRead = (notification) => {
    if (notification.read_at) return;
    
    router.post(route('notifications.read', notification.id), {}, {
        preserveScroll: true,
        only: ['notifications'],
    });
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-hidden flex flex-col"
            >
                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    <Link
                        :href="route('notifications.index')"
                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                        @click="isOpen = false"
                    >
                        View all
                    </Link>
                </div>

                <!-- Notifications List -->
                <div class="overflow-y-auto max-h-80">
                    <div v-if="recentNotifications.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="notification in recentNotifications"
                            :key="notification.id"
                            @click="markAsRead(notification)"
                            :class="[
                                'px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors',
                                !notification.read_at ? 'bg-blue-50 dark:bg-blue-900/20' : ''
                            ]"
                        >
                            <div class="flex gap-3">
                                <!-- Icon -->
                                <div :class="['flex-shrink-0 mt-0.5', getNotificationColor(notification)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification)" />
                                    </svg>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">
                                        {{ notification.data?.title || 'Notification' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ notification.data?.message || notification.data?.body || '' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                        {{ formatDate(notification.created_at) }}
                                    </p>
                                </div>

                                <!-- Unread indicator -->
                                <div v-if="!notification.read_at" class="flex-shrink-0 mt-1">
                                    <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-4 py-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>


