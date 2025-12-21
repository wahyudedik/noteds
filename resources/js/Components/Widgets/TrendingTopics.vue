<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    topics: {
        type: Array,
        default: () => [],
    },
});

const filterByTopic = (topic) => {
    router.get(route('home'), { purpose_type: topic.id === 'all' ? null : topic.id }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <div class="space-y-2">
        <button
            v-for="topic in topics"
            :key="topic.id"
            @click="filterByTopic(topic)"
            class="w-full text-left block px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        >
            <div class="font-medium text-gray-900 dark:text-gray-100">
                {{ topic.name }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ topic.count }} posts
            </div>
        </button>
    </div>
</template>
