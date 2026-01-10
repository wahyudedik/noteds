<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    history: {
        type: Array,
        required: true,
    },
    comment: {
        type: Object,
        required: true,
    },
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(item, index) in history"
            :key="item.id"
            class="border-l-2 border-gray-300 dark:border-gray-600 pl-3 py-2"
        >
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <Link
                        v-if="item.user"
                        :href="route('profile.show', item.user.id)"
                        class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        {{ item.user.business_name || item.user.name }}
                    </Link>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatDate(item.edited_at) }}
                    </span>
                </div>
                <span v-if="index === 0" class="text-xs text-gray-500 dark:text-gray-400 italic">
                    Current version
                </span>
            </div>
            <div
                class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm max-w-none"
                v-html="item.content"
            ></div>
        </div>
    </div>
</template>

