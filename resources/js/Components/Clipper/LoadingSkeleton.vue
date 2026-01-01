<script setup>
defineProps({
    type: {
        type: String,
        default: 'card', // card, list, form
    },
    count: {
        type: Number,
        default: 1,
    },
});
</script>

<template>
    <div v-if="type === 'card'" class="space-y-4">
        <div v-for="i in count" :key="i" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
        </div>
    </div>
    
    <div v-else-if="type === 'list'" class="space-y-3">
        <div v-for="i in count" :key="i" class="flex items-center space-x-4 animate-pulse">
            <div class="h-12 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
        </div>
    </div>
    
    <div v-else-if="type === 'form'" class="space-y-6 animate-pulse">
        <div v-for="i in count" :key="i" class="space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
        </div>
    </div>
</template>

