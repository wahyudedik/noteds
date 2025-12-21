<script setup>
defineProps({
    stats: {
        type: Object,
        default: () => ({
            posts_today: 0,
            posts_this_week: 0,
            total_users: 0,
            total_posts: 0,
        }),
    },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-3">
        <!-- Posts Today -->
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ stats.posts_today || 0 }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Posts Today
                </div>
            </div>
        </div>

        <!-- Posts This Week -->
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ stats.posts_this_week || 0 }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    This Week
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ stats.total_users || 0 }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Active Users
                </div>
            </div>
        </div>

        <!-- Total Posts -->
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ stats.total_posts || 0 }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Total Posts
                </div>
            </div>
        </div>
    </div>
</template>
