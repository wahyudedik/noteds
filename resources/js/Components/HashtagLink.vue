<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    hashtag: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="route('hashtags.show', props.hashtag.slug)"
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-900 dark:text-indigo-200 dark:hover:bg-indigo-800 transition"
    >
        #{{ props.hashtag.name }}
    </Link>
</template>

