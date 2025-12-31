<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    },
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
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    
    return notificationDate.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const markAsRead = (notification) => {
    if (notification.read_at) return;
    
    router.post(route('notifications.read', notification.id), {}, {
        preserveScroll: true,
        only: ['notifications'],
    });
};
</script>

<template>
    <div
        @click="markAsRead(notification)"
        :class="[
            'bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4 cursor-pointer hover:shadow-md transition-all',
            notification.read_at 
                ? 'border-gray-200 dark:border-gray-700' 
                : 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20'
        ]"
    >
        <div class="flex gap-4">
            <!-- Icon -->
            <div :class="['flex-shrink-0', getNotificationColor(notification)]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification)" />
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.data?.title || 'Notification' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ notification.data?.message || notification.data?.body || '' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <span v-if="!notification.read_at" class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDate(notification.created_at) }}
                </div>
            </div>
        </div>
    </div>
</template>

