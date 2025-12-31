<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    documentation: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="route('documentations.show', documentation.slug)"
        class="block p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-lg transition"
    >
        <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ documentation.title }}
            </h3>
            <span
                v-if="documentation.category"
                class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded"
            >
                {{ documentation.category }}
            </span>
        </div>
        <p v-if="documentation.excerpt" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
            {{ documentation.excerpt }}
        </p>
        <div class="mt-4 flex items-center text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            {{ documentation.views_count || 0 }} views
        </div>
    </Link>
</template>

