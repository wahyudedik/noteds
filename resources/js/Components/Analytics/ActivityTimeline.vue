<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Recent Activity
        </h3>
        <div v-if="activities.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No recent activity</p>
        </div>
        <div v-else class="space-y-4">
            <Link
                v-for="activity in activities"
                :key="`${activity.type}-${activity.created_at}`"
                :href="activity.url"
                class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <div class="flex-shrink-0">
                    <div
                        :class="[
                            'h-10 w-10 rounded-full flex items-center justify-center',
                            activity.type === 'post'
                                ? 'bg-indigo-100 dark:bg-indigo-900'
                                : 'bg-purple-100 dark:bg-purple-900'
                        ]"
                    >
                        <svg
                            v-if="activity.type === 'post'"
                            class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5 text-purple-600 dark:text-purple-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ activity.title }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ formatDate(activity.created_at) }}
                    </p>
                </div>
            </Link>
        </div>
    </div>
</template>

