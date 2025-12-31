<script setup>
import NotificationItem from './NotificationItem.vue';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <div v-if="notifications.length > 0" class="space-y-3">
            <NotificationItem
                v-for="notification in notifications"
                :key="notification.id"
                :notification="notification"
            />
        </div>
        <div v-else class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">No notifications found.</p>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading" class="mt-6 text-center py-8">
            <div class="inline-flex items-center space-x-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Loading more notifications...</span>
            </div>
        </div>
    </div>
</template>

