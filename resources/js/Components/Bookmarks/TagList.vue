<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    tags: {
        type: Array,
        default: () => [],
    },
    clickable: {
        type: Boolean,
        default: true,
    },
});

const filterByTag = (tag) => {
    if (props.clickable) {
        router.visit(route('bookmarks.tags.show', tag.id));
    }
};
</script>

<template>
    <div class="flex flex-wrap gap-1">
        <button
            v-for="tag in tags"
            :key="tag.id"
            @click="filterByTag(tag)"
            :class="[
                'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium transition',
                clickable
                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-800 cursor-pointer'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            ]"
        >
            {{ tag.name }}
            <span
                v-if="tag.is_global"
                class="text-xs opacity-75"
                title="Global tag"
            >
                🌐
            </span>
            <span
                v-if="tag.usage_count"
                class="text-xs opacity-75"
            >
                ({{ tag.usage_count }})
            </span>
        </button>
    </div>
</template>

